<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\Statistics;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 统计数据表格渲染器
 */
class StatisticsTableRenderer
{
    public function __construct(
        private readonly StatisticsFormatter $formatter,
    ) {
    }

    /**
     * 表格格式输出
     * @param array<string, mixed> $statistics
     */
    public function outputTable(SymfonyStyle $io, array $statistics, bool $detailed): void
    {
        $this->renderBasicStatisticsTable($io, $statistics);
        $this->renderCourseStatisticsTable($io, $statistics);

        if ($detailed) {
            $this->renderTopCoursesTableIfAvailable($io, $statistics);
        }
    }

    /**
     * 渲染基础统计表格
     * @param array<string, mixed> $statistics
     */
    public function renderBasicStatisticsTable(SymfonyStyle $io, array $statistics): void
    {
        $io->section('基础统计');
        $basicTable = new Table($io);
        $basicTable->setHeaders(['指标', '数值']);

        $basic = $statistics['basic'] ?? [];
        /** @var array<string, mixed> $basicArray */
        $basicArray = is_array($basic) ? $basic : [];
        $this->addTableRowsFromData($basicTable, $basicArray, true);
        $basicTable->render();
    }

    /**
     * 渲染课程统计表格
     * @param array<string, mixed> $statistics
     */
    public function renderCourseStatisticsTable(SymfonyStyle $io, array $statistics): void
    {
        $io->section('课程统计');
        $courseTable = new Table($io);
        $courseTable->setHeaders(['类型', '数量']);

        if (isset($statistics['courses']) && is_array($statistics['courses'])) {
            $byStatus = $statistics['courses']['by_status'] ?? [];
            /** @var array<string, mixed> $byStatusArray */
            $byStatusArray = is_array($byStatus) ? $byStatus : [];
            $this->addTableRowsFromData($courseTable, $byStatusArray, false);
        }
        $courseTable->render();
    }

    /**
     * 从数据添加表格行
     * @param array<string, mixed> $data
     */
    private function addTableRowsFromData(Table $table, array $data, bool $formatLabel): void
    {
        foreach ($data as $key => $value) {
            $label = $formatLabel ? $this->formatter->formatLabel($key) : ucfirst($key);
            $valueStr = is_scalar($value) ? (string) $value : '';
            $table->addRow([$label, $valueStr]);
        }
    }

    /**
     * 如果可用则渲染热门课程排行榜
     * @param array<string, mixed> $statistics
     */
    private function renderTopCoursesTableIfAvailable(SymfonyStyle $io, array $statistics): void
    {
        $topCourses = $this->extractTopCourses($statistics);
        if (0 === count($topCourses)) {
            return;
        }

        $this->renderTopCoursesTable($io, $topCourses);
    }

    /**
     * 提取热门课程数据
     * @param array<string, mixed> $statistics
     * @return array<int, array<string, mixed>>
     */
    private function extractTopCourses(array $statistics): array
    {
        $detailed = $statistics['detailed'] ?? null;
        if (!is_array($detailed)) {
            return [];
        }

        $topCourses = $detailed['top_courses'] ?? null;
        if (!is_array($topCourses)) {
            return [];
        }

        // 过滤并确保每个元素都是数组类型
        $result = [];
        foreach (array_values($topCourses) as $course) {
            if (is_array($course)) {
                /** @var array<string, mixed> $course */
                $result[] = $course;
            }
        }

        /** @var array<int, array<string, mixed>> $result */
        return $result;
    }

    /**
     * 渲染热门课程表格
     * @param array<int, array<string, mixed>> $topCourses
     */
    private function renderTopCoursesTable(SymfonyStyle $io, array $topCourses): void
    {
        $io->section('热门课程排行榜');
        $topTable = new Table($io);
        $topTable->setHeaders(['排名', '课程标题', '受欢迎度', '质量分数', '收藏数', '评价数', '平均评分']);

        foreach ($topCourses as $index => $course) {
            $topTable->addRow($this->buildTopCourseRow($index, $course));
        }
        $topTable->render();
    }

    /**
     * 构建热门课程行数据
     * @param array<string, mixed> $course
     * @return array<int, string>
     */
    private function buildTopCourseRow(int $index, array $course): array
    {
        $title = $course['title'] ?? '';
        $popularityScore = $course['popularity_score'] ?? '';
        $qualityScore = $course['quality_score'] ?? '';
        $collectCount = $course['collect_count'] ?? '';
        $evaluateCount = $course['evaluate_count'] ?? '';
        $averageRating = $course['average_rating'] ?? '';

        return [
            (string) ($index + 1),
            is_scalar($title) ? (string) $title : '',
            is_scalar($popularityScore) ? (string) $popularityScore : '',
            is_scalar($qualityScore) ? (string) $qualityScore : '',
            is_scalar($collectCount) ? (string) $collectCount : '',
            is_scalar($evaluateCount) ? (string) $evaluateCount : '',
            is_scalar($averageRating) ? (string) $averageRating : '',
        ];
    }

    /**
     * 渲染课程基础信息
     * @param array<string, mixed> $report
     */
    public function renderCourseBasicInfo(SymfonyStyle $io, array $report): void
    {
        $io->section('基础信息');
        $basicTable = new Table($io);
        $basicTable->setHeaders(['属性', '值']);

        $courseInfo = $report['course_info'] ?? [];
        /** @var array<string, mixed> $courseInfoArray */
        $courseInfoArray = is_array($courseInfo) ? $courseInfo : [];
        $this->addTableRowsFromData($basicTable, $courseInfoArray, true);
        $basicTable->render();
    }

    /**
     * 渲染受欢迎度指标
     * @param array<string, mixed> $report
     */
    public function renderPopularityMetrics(SymfonyStyle $io, array $report): void
    {
        $io->section('受欢迎程度指标');
        $popularityTable = new Table($io);
        $popularityTable->setHeaders(['指标', '值']);

        $metrics = $report['popularity_metrics'] ?? [];
        /** @var array<string, mixed> $metricsArray */
        $metricsArray = is_array($metrics) ? $metrics : [];
        $this->addFormattedMetricsToTable($popularityTable, $metricsArray);
        $popularityTable->render();
    }

    /**
     * 添加格式化的指标到表格
     * @param array<string, mixed> $metrics
     */
    private function addFormattedMetricsToTable(Table $table, array $metrics): void
    {
        foreach ($metrics as $key => $value) {
            $label = $this->formatter->formatLabel($key);
            $formattedValue = $this->formatter->formatMetricValue($value);
            $table->addRow([$label, $formattedValue]);
        }
    }

    /**
     * 如果可用则渲染改进建议
     * @param array<string, mixed> $report
     */
    public function renderRecommendationsIfAvailable(SymfonyStyle $io, array $report): void
    {
        $recommendations = $this->extractRecommendations($report);
        if (0 === count($recommendations)) {
            return;
        }

        $io->section('改进建议');
        $this->displayRecommendations($io, $recommendations);
    }

    /**
     * 提取改进建议
     * @param array<string, mixed> $report
     * @return array<int, array<string, mixed>>
     */
    private function extractRecommendations(array $report): array
    {
        $recommendations = $report['recommendations'] ?? null;
        if (!is_array($recommendations) || 0 === count($recommendations)) {
            return [];
        }

        // 过滤并确保每个元素都是数组类型
        $result = [];
        foreach (array_values($recommendations) as $recommendation) {
            if (is_array($recommendation)) {
                /** @var array<string, mixed> $recommendation */
                $result[] = $recommendation;
            }
        }

        /** @var array<int, array<string, mixed>> $result */
        return $result;
    }

    /**
     * 显示改进建议列表
     * @param array<int, array<string, mixed>> $recommendations
     */
    private function displayRecommendations(SymfonyStyle $io, array $recommendations): void
    {
        foreach ($recommendations as $recommendation) {
            if ($this->isValidRecommendation($recommendation)) {
                $this->displayRecommendation($io, $recommendation);
            }
        }
    }

    /**
     * 检查是否为有效的建议
     * @param mixed $recommendation
     */
    private function isValidRecommendation(mixed $recommendation): bool
    {
        return is_array($recommendation)
            && isset($recommendation['priority'], $recommendation['message']);
    }

    /**
     * 显示单个建议
     * @param array<string, mixed> $recommendation
     */
    private function displayRecommendation(SymfonyStyle $io, array $recommendation): void
    {
        $priority = is_string($recommendation['priority']) ? strtoupper($recommendation['priority']) : 'INFO';
        $message = is_string($recommendation['message']) ? $recommendation['message'] : '';
        $io->text(sprintf('[%s] %s', $priority, $message));
    }
}
