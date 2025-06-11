<?php

namespace Tourze\TrainCourseBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程备份命令
 * 
 * 用于备份课程数据，支持全量备份和增量备份
 */
#[AsCommand(
    name: 'course:backup',
    description: '备份课程数据'
)]
class CourseBackupCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CourseRepository $courseRepository,
        private readonly CourseConfigService $configService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                '备份类型 (full|incremental)',
                'full'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                '备份文件输出路径',
                '/tmp/course_backup'
            )
            ->addOption(
                'since',
                's',
                InputOption::VALUE_OPTIONAL,
                '增量备份起始时间 (Y-m-d H:i:s)'
            )
            ->addOption(
                'compress',
                'c',
                InputOption::VALUE_NONE,
                '是否压缩备份文件'
            )
            ->addOption(
                'include-media',
                'm',
                InputOption::VALUE_NONE,
                '是否包含媒体文件'
            )
            ->setHelp('
此命令用于备份课程数据。

示例:
  # 全量备份
  php bin/console course:backup

  # 增量备份（备份最近7天的数据）
  php bin/console course:backup --type=incremental --since="7 days ago"

  # 压缩备份并包含媒体文件
  php bin/console course:backup --compress --include-media

  # 指定输出路径
  php bin/console course:backup --output=/backup/courses
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $type = $input->getOption('type');
        $outputPath = $input->getOption('output');
        $since = $input->getOption('since');
        $compress = $input->getOption('compress');
        $includeMedia = $input->getOption('include-media');

        $io->title('课程数据备份');

        try {
            // 验证参数
            if (!in_array($type, ['full', 'incremental'])) {
                $io->error('备份类型必须是 full 或 incremental');
                return Command::FAILURE;
            }

            if ($type === 'incremental' && !$since) {
                $io->error('增量备份必须指定起始时间');
                return Command::FAILURE;
            }

            // 创建备份目录
            $backupDir = $this->createBackupDirectory($outputPath);
            $io->info("备份目录: {$backupDir}");

            // 执行备份
            $backupInfo = match ($type) {
                'full' => $this->performFullBackup($backupDir, $includeMedia, $io),
                'incremental' => $this->performIncrementalBackup($backupDir, $since, $includeMedia, $io)
            };

            // 压缩备份文件
            if ($compress) {
                $archivePath = $this->compressBackup($backupDir, $io);
                $backupInfo['archive_path'] = $archivePath;
            }

            // 生成备份报告
            $this->generateBackupReport($backupDir, $backupInfo, $io);

            $io->success('备份完成');
            $io->table(['项目', '值'], [
                ['备份类型', $type],
                ['备份目录', $backupDir],
                ['课程数量', $backupInfo['course_count']],
                ['章节数量', $backupInfo['chapter_count']],
                ['课时数量', $backupInfo['lesson_count']],
                ['文件大小', $this->formatBytes($backupInfo['total_size'])],
                ['备份时间', date('Y-m-d H:i:s')]
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error("备份失败: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * 创建备份目录
     */
    private function createBackupDirectory(string $basePath): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupDir = rtrim($basePath, '/') . "/course_backup_{$timestamp}";
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        return $backupDir;
    }

    /**
     * 执行全量备份
     */
    private function performFullBackup(string $backupDir, bool $includeMedia, SymfonyStyle $io): array
    {
        $io->section('执行全量备份');

        // 备份课程数据
        $courses = $this->courseRepository->findAll();
        $courseData = [];
        $chapterCount = 0;
        $lessonCount = 0;

        $progressBar = $io->createProgressBar(count($courses));
        $progressBar->start();

        foreach ($courses as $course) {
            $courseArray = $this->serializeCourse($course);
            $courseData[] = $courseArray;
            
            $chapterCount += count($courseArray['chapters'] ?? []);
            foreach ($courseArray['chapters'] ?? [] as $chapter) {
                $lessonCount += count($chapter['lessons'] ?? []);
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $io->newLine(2);

        // 保存课程数据
        $dataFile = $backupDir . '/courses.json';
        file_put_contents($dataFile, json_encode($courseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 备份媒体文件
        $mediaSize = 0;
        if ($includeMedia) {
            $mediaSize = $this->backupMediaFiles($backupDir, $courses, $io);
        }

        return [
            'course_count' => count($courses),
            'chapter_count' => $chapterCount,
            'lesson_count' => $lessonCount,
            'total_size' => filesize($dataFile) + $mediaSize
        ];
    }

    /**
     * 执行增量备份
     */
    private function performIncrementalBackup(string $backupDir, string $since, bool $includeMedia, SymfonyStyle $io): array
    {
        $io->section('执行增量备份');

        $sinceDate = new \DateTime($since);
        $courses = $this->courseRepository->findUpdatedSince($sinceDate);
        
        $io->info("找到 " . count($courses) . " 个更新的课程");

        if (empty($courses)) {
            $io->warning('没有找到需要备份的课程');
            return [
                'course_count' => 0,
                'chapter_count' => 0,
                'lesson_count' => 0,
                'total_size' => 0
            ];
        }

        // 备份更新的课程
        $courseData = [];
        $chapterCount = 0;
        $lessonCount = 0;

        foreach ($courses as $course) {
            $courseArray = $this->serializeCourse($course);
            $courseData[] = $courseArray;
            
            $chapterCount += count($courseArray['chapters'] ?? []);
            foreach ($courseArray['chapters'] ?? [] as $chapter) {
                $lessonCount += count($chapter['lessons'] ?? []);
            }
        }

        // 保存增量数据
        $dataFile = $backupDir . '/incremental_courses.json';
        file_put_contents($dataFile, json_encode([
            'since' => $since,
            'backup_time' => date('Y-m-d H:i:s'),
            'courses' => $courseData
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 备份媒体文件
        $mediaSize = 0;
        if ($includeMedia) {
            $mediaSize = $this->backupMediaFiles($backupDir, $courses, $io);
        }

        return [
            'course_count' => count($courses),
            'chapter_count' => $chapterCount,
            'lesson_count' => $lessonCount,
            'total_size' => filesize($dataFile) + $mediaSize
        ];
    }

    /**
     * 序列化课程数据
     */
    private function serializeCourse($course): array
    {
        // 这里应该根据实际的实体结构来序列化
        // 简化实现，实际应该包含所有相关数据
        return [
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'description' => $course->getDescription(),
            'cover_thumb' => $course->getCoverThumb(),
            'price' => $course->getPrice(),
            'valid_day' => $course->getValidDay(),
            'learn_hour' => $course->getLearnHour(),
            'teacher_name' => $course->getTeacherName(),
            'instructor' => $course->getInstructor(),
            'valid' => $course->isValid(),
            'create_time' => $course->getCreateTime()?->format('Y-m-d H:i:s'),
            'update_time' => $course->getUpdateTime()?->format('Y-m-d H:i:s'),
            // 这里应该包含章节、课时等相关数据
            'chapters' => [], // 实际实现中应该序列化章节数据
        ];
    }

    /**
     * 备份媒体文件
     */
    private function backupMediaFiles(string $backupDir, array $courses, SymfonyStyle $io): int
    {
        $io->section('备份媒体文件');
        
        $mediaDir = $backupDir . '/media';
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0755, true);
        }

        $totalSize = 0;
        
        // 这里应该根据实际的媒体文件存储方式来实现
        // 简化实现
        $io->info('媒体文件备份功能待实现');
        
        return $totalSize;
    }

    /**
     * 压缩备份文件
     */
    private function compressBackup(string $backupDir, SymfonyStyle $io): string
    {
        $io->section('压缩备份文件');
        
        $archivePath = $backupDir . '.tar.gz';
        
        // 使用tar命令压缩
        $command = "tar -czf {$archivePath} -C " . dirname($backupDir) . " " . basename($backupDir);
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $io->success("备份文件已压缩: {$archivePath}");
            // 删除原始目录
            $this->removeDirectory($backupDir);
        } else {
            $io->warning('压缩失败，保留原始备份目录');
        }
        
        return $archivePath;
    }

    /**
     * 生成备份报告
     */
    private function generateBackupReport(string $backupDir, array $backupInfo, SymfonyStyle $io): void
    {
        $reportFile = $backupDir . '/backup_report.txt';
        
        $report = [
            "课程备份报告",
            "=" . str_repeat("=", 20),
            "备份时间: " . date('Y-m-d H:i:s'),
            "备份目录: {$backupDir}",
            "课程数量: {$backupInfo['course_count']}",
            "章节数量: {$backupInfo['chapter_count']}",
            "课时数量: {$backupInfo['lesson_count']}",
            "文件大小: " . $this->formatBytes($backupInfo['total_size']),
            "",
            "备份完成"
        ];
        
        file_put_contents($reportFile, implode("\n", $report));
        $io->info("备份报告已生成: {$reportFile}");
    }

    /**
     * 格式化字节大小
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 删除目录
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
} 