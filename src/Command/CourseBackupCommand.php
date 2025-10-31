<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\BackupStrategy\BackupStrategyInterface;
use Tourze\TrainCourseBundle\Service\BackupStrategy\FullBackupStrategy;
use Tourze\TrainCourseBundle\Service\BackupStrategy\IncrementalBackupStrategy;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupCompressor;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupDirectoryManager;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupReportGenerator;

/**
 * 课程备份命令
 *
 * 用于备份课程数据，支持全量备份和增量备份
 */
#[AsCommand(name: self::NAME, description: '备份课程数据')]
class CourseBackupCommand extends Command
{
    public const NAME = 'course:backup';

    public function __construct(
        private readonly FullBackupStrategy $fullBackupStrategy,
        private readonly IncrementalBackupStrategy $incrementalBackupStrategy,
        private readonly BackupDirectoryManager $directoryManager,
        private readonly BackupCompressor $compressor,
        private readonly BackupReportGenerator $reportGenerator,
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
            ->setHelp(<<<'TXT'
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
                TXT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $options = $this->parseInputOptions($input);

        $io->title('课程数据备份');

        try {
            $this->validateOptions($options, $io);

            // 创建备份目录
            $backupDir = $this->directoryManager->createBackupDirectory($options['outputPath']);
            $io->info("备份目录: {$backupDir}");

            // 执行备份
            $backupInfo = $this->executeBackup($options['type'], $backupDir, $options['includeMedia'], $io);

            // 生成备份报告（必须在压缩之前，因为压缩后会删除原始目录）
            $this->reportGenerator->generateBackupReport($backupDir, $backupInfo, $io);

            // 压缩备份文件
            if ($options['compress']) {
                $archivePath = $this->compressor->compressBackup($backupDir, $io);
                $backupInfo['archive_path'] = $archivePath;
            }

            $this->displayBackupSummary($io, $options, $backupDir, $backupInfo);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error("备份失败: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * 解析输入选项
     * @return array{type: string, outputPath: string, since: string|null, compress: bool, includeMedia: bool}
     */
    private function parseInputOptions(InputInterface $input): array
    {
        $type = $input->getOption('type');
        $outputPath = $input->getOption('output');
        $since = $input->getOption('since');

        return [
            'type' => is_string($type) ? $type : 'full',
            'outputPath' => is_string($outputPath) ? $outputPath : '/tmp/course_backup',
            'since' => is_string($since) ? $since : null,
            'compress' => (bool) $input->getOption('compress'),
            'includeMedia' => (bool) $input->getOption('include-media'),
        ];
    }

    /**
     * 验证选项
     * @param array{type: string, outputPath: string, since: string|null, compress: bool, includeMedia: bool} $options
     */
    private function validateOptions(array $options, SymfonyStyle $io): void
    {
        if (!in_array($options['type'], ['full', 'incremental'], true)) {
            $io->error('备份类型必须是 full 或 incremental');
            throw new \InvalidArgumentException('无效的备份类型');
        }

        if ('incremental' === $options['type'] && (null === $options['since'] || '' === $options['since'])) {
            $io->error('增量备份必须指定起始时间');
            throw new \InvalidArgumentException('增量备份需要起始时间');
        }
    }

    /**
     * 执行备份
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    private function executeBackup(string $type, string $backupDir, bool $includeMedia, SymfonyStyle $io): array
    {
        $strategy = $this->getBackupStrategy($type);

        return $strategy->backup($backupDir, $includeMedia, $io);
    }

    /**
     * 获取备份策略
     */
    private function getBackupStrategy(string $type): BackupStrategyInterface
    {
        return match ($type) {
            'full' => $this->fullBackupStrategy,
            'incremental' => $this->incrementalBackupStrategy,
            default => throw new \InvalidArgumentException("不支持的备份类型: {$type}"),
        };
    }

    /**
     * 显示备份摘要
     * @param array{type: string, outputPath: string, since: string|null, compress: bool, includeMedia: bool} $options
     * @param array{course_count: int, chapter_count: int, lesson_count: int, total_size: int} $backupInfo
     */
    private function displayBackupSummary(SymfonyStyle $io, array $options, string $backupDir, array $backupInfo): void
    {
        $io->success('备份完成');
        $io->table(['项目', '值'], [
            ['备份类型', $options['type']],
            ['备份目录', $backupDir],
            ['课程数量', (string) $backupInfo['course_count']],
            ['章节数量', (string) $backupInfo['chapter_count']],
            ['课时数量', (string) $backupInfo['lesson_count']],
            ['文件大小', $this->formatBytes($backupInfo['total_size'])],
            ['备份时间', date('Y-m-d H:i:s')],
        ]);
    }

    /**
     * 格式化字节大小
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes > 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * (int) $pow));

        return round($bytes, 2) . ' ' . $units[(int) $pow];
    }
}
