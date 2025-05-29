<?php

namespace Tourze\TrainCourseBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CollectRepository;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;
use Tourze\TrainCourseBundle\Service\CourseAnalyticsService;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程统计命令
 * 
 * 生成课程相关的统计报告，包括课程数量、评价统计、收藏统计等
 */
#[AsCommand(
    name: 'train-course:statistics',
    description: '生成课程统计报告'
)]
class CourseStatisticsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseRepository $courseRepository,
        private CollectRepository $collectRepository,
        private EvaluateRepository $evaluateRepository,
        private CourseAuditRepository $auditRepository,
        private CourseVersionRepository $versionRepository,
        private CourseAnalyticsService $analyticsService,
        private CourseConfigService $configService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, '输出格式 (table|json|csv)', 'table')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, '输出文件路径')
            ->addOption('detailed', null, InputOption::VALUE_NONE, '显示详细统计信息')
            ->addOption('course-id', null, InputOption::VALUE_OPTIONAL, '指定课程ID获取详细统计')
            ->addOption('top', null, InputOption::VALUE_OPTIONAL, '显示排行榜前N名', 10)
            ->setHelp('
该命令用于生成课程统计报告：

<info>基础统计：</info>
  <comment>php bin/console train-course:statistics</comment>

<info>详细统计：</info>
  <comment>php bin/console train-course:statistics --detailed</comment>

<info>指定课程统计：</info>
  <comment>php bin/console train-course:statistics --course-id=123</comment>

<info>JSON格式输出：</info>
  <comment>php bin/console train-course:statistics --format=json</comment>

<info>输出到文件：</info>
  <comment>php bin/console train-course:statistics --output=/path/to/report.json --format=json</comment>

<info>排行榜：</info>
  <comment>php bin/console train-course:statistics --top=20</comment>
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        $outputFile = $input->getOption('output');
        $detailed = $input->getOption('detailed');
        $courseId = $input->getOption('course-id');
        $topCount = (int) $input->getOption('top');

        if ($courseId) {
            return $this->showCourseStatistics($io, $courseId, $format, $outputFile);
        }

        $io->title('课程统计报告');

        // 收集统计数据
        $statistics = $this->collectStatistics($detailed, $topCount);

        // 根据格式输出
        switch ($format) {
            case 'json':
                $this->outputJson($io, $statistics, $outputFile);
                break;
            case 'csv':
                $this->outputCsv($io, $statistics, $outputFile);
                break;
            default:
                $this->outputTable($io, $statistics, $detailed);
                break;
        }

        $io->success('统计报告生成完成');
        return Command::SUCCESS;
    }

    /**
     * 收集统计数据
     */
    private function collectStatistics(bool $detailed, int $topCount): array
    {
        $statistics = [
            'basic' => $this->getBasicStatistics(),
            'courses' => $this->getCourseStatistics(),
            'engagement' => $this->getEngagementStatistics(),
            'audit' => $this->getAuditStatistics(),
            'version' => $this->getVersionStatistics(),
        ];

        if ($detailed) {
            $statistics['detailed'] = [
                'top_courses' => $this->getTopCourses($topCount),
                'category_stats' => $this->getCategoryStatistics(),
                'monthly_trends' => $this->getMonthlyTrends(),
            ];
        }

        return $statistics;
    }

    /**
     * 获取基础统计信息
     */
    private function getBasicStatistics(): array
    {
        $totalCourses = $this->courseRepository->count([]);
        $validCourses = count($this->courseRepository->findValidCourses());
        $totalCollects = $this->collectRepository->count([]);
        $totalEvaluates = $this->evaluateRepository->count([]);

        return [
            'total_courses' => $totalCourses,
            'valid_courses' => $validCourses,
            'invalid_courses' => $totalCourses - $validCourses,
            'total_collects' => $totalCollects,
            'total_evaluates' => $totalEvaluates,
            'average_collects_per_course' => $totalCourses > 0 ? round($totalCollects / $totalCourses, 2) : 0,
            'average_evaluates_per_course' => $totalCourses > 0 ? round($totalEvaluates / $totalCourses, 2) : 0,
        ];
    }

    /**
     * 获取课程统计信息
     */
    private function getCourseStatistics(): array
    {
        $courseStats = $this->courseRepository->getStatistics();
        
        return [
            'by_status' => $courseStats,
            'with_chapters' => count($this->courseRepository->findCoursesWithChapters()),
            'with_videos' => count($this->courseRepository->findCoursesWithVideos()),
            'free_courses' => count($this->courseRepository->findFreeCourses()),
            'paid_courses' => count($this->courseRepository->findPaidCourses()),
        ];
    }

    /**
     * 获取参与度统计信息
     */
    private function getEngagementStatistics(): array
    {
        $collectStats = $this->collectRepository->getCollectStatistics();
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics();

        return [
            'collects' => $collectStats,
            'evaluates' => $evaluateStats,
            'engagement_rate' => $this->calculateEngagementRate($collectStats, $evaluateStats),
        ];
    }

    /**
     * 获取审核统计信息
     */
    private function getAuditStatistics(): array
    {
        return $this->auditRepository->getAuditStatistics();
    }

    /**
     * 获取版本统计信息
     */
    private function getVersionStatistics(): array
    {
        return $this->versionRepository->getVersionStatistics();
    }

    /**
     * 获取热门课程排行榜
     */
    private function getTopCourses(int $limit): array
    {
        $rankings = $this->analyticsService->getCourseRankings([
            'sort_by' => 'popularity_score',
            'limit' => $limit,
        ]);

        return array_map(function($ranking) {
            return [
                'id' => $ranking['course']->getId(),
                'title' => $ranking['course']->getTitle(),
                'popularity_score' => $ranking['popularity_score'],
                'quality_score' => $ranking['quality_score'],
                'collect_count' => $ranking['collect_count'],
                'evaluate_count' => $ranking['evaluate_count'],
                'average_rating' => $ranking['average_rating'],
            ];
        }, $rankings);
    }

    /**
     * 获取分类统计信息
     */
    private function getCategoryStatistics(): array
    {
        // 这里需要根据实际的分类实体来实现
        // 暂时返回空数组
        return [];
    }

    /**
     * 获取月度趋势数据
     */
    private function getMonthlyTrends(): array
    {
        $trends = [];
        $months = 6; // 最近6个月

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new \DateTime(sprintf('-%d months', $i));
            $monthKey = $date->format('Y-m');
            
            $trends[$monthKey] = [
                'new_courses' => $this->courseRepository->countByMonth($date),
                'new_collects' => $this->collectRepository->countByMonth($date),
                'new_evaluates' => $this->evaluateRepository->countByMonth($date),
            ];
        }

        return $trends;
    }

    /**
     * 计算参与度
     */
    private function calculateEngagementRate(array $collectStats, array $evaluateStats): float
    {
        $totalCourses = $this->courseRepository->count([]);
        if ($totalCourses === 0) {
            return 0;
        }

        $engagedCourses = count(array_unique(array_merge(
            array_column($collectStats['by_course'] ?? [], 'course_id'),
            array_column($evaluateStats['by_course'] ?? [], 'course_id')
        )));

        return round($engagedCourses / $totalCourses * 100, 2);
    }

    /**
     * 显示指定课程的统计信息
     */
    private function showCourseStatistics(SymfonyStyle $io, string $courseId, string $format, ?string $outputFile): int
    {
        $course = $this->courseRepository->find($courseId);
        if (!$course) {
            $io->error(sprintf('课程 ID %s 不存在', $courseId));
            return Command::FAILURE;
        }

        $report = $this->analyticsService->getCourseAnalyticsReport($course);

        switch ($format) {
            case 'json':
                $this->outputJson($io, $report, $outputFile);
                break;
            default:
                $this->displayCourseReport($io, $course, $report);
                break;
        }

        return Command::SUCCESS;
    }

    /**
     * 表格格式输出
     */
    private function outputTable(SymfonyStyle $io, array $statistics, bool $detailed): void
    {
        // 基础统计表格
        $io->section('基础统计');
        $basicTable = new Table($output ?? $io);
        $basicTable->setHeaders(['指标', '数值']);
        
        foreach ($statistics['basic'] as $key => $value) {
            $basicTable->addRow([ucfirst(str_replace('_', ' ', $key)), $value]);
        }
        $basicTable->render();

        // 课程统计表格
        $io->section('课程统计');
        $courseTable = new Table($output ?? $io);
        $courseTable->setHeaders(['类型', '数量']);
        
        foreach ($statistics['courses']['by_status'] as $status => $count) {
            $courseTable->addRow([ucfirst($status), $count]);
        }
        $courseTable->render();

        if ($detailed && isset($statistics['detailed']['top_courses'])) {
            $io->section('热门课程排行榜');
            $topTable = new Table($output ?? $io);
            $topTable->setHeaders(['排名', '课程标题', '受欢迎度', '质量分数', '收藏数', '评价数', '平均评分']);
            
            foreach ($statistics['detailed']['top_courses'] as $index => $course) {
                $topTable->addRow([
                    $index + 1,
                    $course['title'],
                    $course['popularity_score'],
                    $course['quality_score'],
                    $course['collect_count'],
                    $course['evaluate_count'],
                    $course['average_rating'],
                ]);
            }
            $topTable->render();
        }
    }

    /**
     * JSON格式输出
     */
    private function outputJson(SymfonyStyle $io, array $data, ?string $outputFile): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($outputFile) {
            file_put_contents($outputFile, $json);
            $io->success(sprintf('统计报告已保存到: %s', $outputFile));
        } else {
            $io->writeln($json);
        }
    }

    /**
     * CSV格式输出
     */
    private function outputCsv(SymfonyStyle $io, array $data, ?string $outputFile): void
    {
        $csv = [];
        
        // 基础统计
        $csv[] = ['类型', '指标', '数值'];
        foreach ($data['basic'] as $key => $value) {
            $csv[] = ['基础统计', $key, $value];
        }

        // 课程统计
        foreach ($data['courses']['by_status'] as $status => $count) {
            $csv[] = ['课程统计', $status, $count];
        }

        if ($outputFile) {
            $fp = fopen($outputFile, 'w');
            foreach ($csv as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
            $io->success(sprintf('CSV报告已保存到: %s', $outputFile));
        } else {
            foreach ($csv as $row) {
                $io->writeln(implode(',', $row));
            }
        }
    }

    /**
     * 显示课程详细报告
     */
    private function displayCourseReport(SymfonyStyle $io, $course, array $report): void
    {
        $io->title(sprintf('课程分析报告: %s', $course->getTitle()));

        // 基础信息
        $io->section('基础信息');
        $basicTable = new Table($output ?? $io);
        $basicTable->setHeaders(['属性', '值']);
        
        foreach ($report['course_info'] as $key => $value) {
            $basicTable->addRow([ucfirst(str_replace('_', ' ', $key)), $value]);
        }
        $basicTable->render();

        // 受欢迎程度指标
        $io->section('受欢迎程度指标');
        $popularityTable = new Table($output ?? $io);
        $popularityTable->setHeaders(['指标', '值']);
        
        foreach ($report['popularity_metrics'] as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $popularityTable->addRow([ucfirst(str_replace('_', ' ', $key)), $value]);
        }
        $popularityTable->render();

        // 改进建议
        if (!empty($report['recommendations'])) {
            $io->section('改进建议');
            foreach ($report['recommendations'] as $recommendation) {
                $io->text(sprintf('[%s] %s', strtoupper($recommendation['priority']), $recommendation['message']));
            }
        }
    }
} 