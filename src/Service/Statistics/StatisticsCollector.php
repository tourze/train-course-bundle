<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\Statistics;

use Tourze\TrainCourseBundle\Repository\CollectRepository;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;
use Tourze\TrainCourseBundle\Service\CourseAnalyticsService;

/**
 * 统计数据收集器
 */
class StatisticsCollector
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly CollectRepository $collectRepository,
        private readonly EvaluateRepository $evaluateRepository,
        private readonly CourseAuditRepository $auditRepository,
        private readonly CourseAnalyticsService $analyticsService,
    ) {
    }

    /**
     * 收集统计数据
     * @return array<string, mixed>
     */
    public function collectStatistics(bool $detailed, int $topCount): array
    {
        $statistics = $this->getBaseStatistics();

        if ($detailed) {
            $statistics['detailed'] = $this->getDetailedStatistics($topCount);
        }

        return $statistics;
    }

    /**
     * 获取基础统计数据
     * @return array<string, mixed>
     */
    private function getBaseStatistics(): array
    {
        return [
            'basic' => $this->getBasicStatistics(),
            'courses' => $this->getCourseStatistics(),
            'engagement' => $this->getEngagementStatistics(),
            'audit' => $this->getAuditStatistics(),
            'version' => $this->getVersionStatistics(),
        ];
    }

    /**
     * 获取详细统计数据
     * @return array<string, mixed>
     */
    private function getDetailedStatistics(int $topCount): array
    {
        return [
            'top_courses' => $this->getTopCourses($topCount),
            'category_stats' => $this->getCategoryStatistics(),
            'monthly_trends' => $this->getMonthlyTrends(),
        ];
    }

    /**
     * 获取基础统计信息
     * @return array<string, mixed>
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
     * @return array<string, mixed>
     */
    private function getCourseStatistics(): array
    {
        $courseStats = $this->courseRepository->getStatistics();

        return [
            'by_status' => $courseStats,
        ];
    }

    /**
     * 获取参与度统计信息
     * @return array<string, mixed>
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
     * @return array<string, mixed>
     */
    private function getAuditStatistics(): array
    {
        return $this->auditRepository->getAuditStatistics();
    }

    /**
     * 获取版本统计信息
     * @return array<string, mixed>
     */
    private function getVersionStatistics(): array
    {
        // 暂时返回空数组，需要确认 getVersionStatistics 方法的参数
        return [];
    }

    /**
     * 获取热门课程排行榜
     * @return array<int, array<string, mixed>>
     */
    private function getTopCourses(int $limit): array
    {
        $rankings = $this->analyticsService->getCourseRankings([
            'sort_by' => 'popularity_score',
            'limit' => $limit,
        ]);

        return array_map(fn ($ranking) => $this->mapRankingToArray($ranking), $rankings);
    }

    /**
     * 将排名数据映射为数组
     * @param array<string, mixed> $ranking
     * @return array<string, mixed>
     */
    private function mapRankingToArray(array $ranking): array
    {
        $course = $ranking['course'] ?? null;

        return [
            'id' => is_object($course) && method_exists($course, 'getId') ? $course->getId() : null,
            'title' => is_object($course) && method_exists($course, 'getTitle') ? $course->getTitle() : null,
            'popularity_score' => $ranking['popularity_score'] ?? 0,
            'quality_score' => $ranking['quality_score'] ?? 0,
            'collect_count' => $ranking['collect_count'] ?? 0,
            'evaluate_count' => $ranking['evaluate_count'] ?? 0,
            'average_rating' => $ranking['average_rating'] ?? 0,
        ];
    }

    /**
     * 获取分类统计信息
     * @return array<string, mixed>
     */
    private function getCategoryStatistics(): array
    {
        // 这里需要根据实际的分类实体来实现
        // 暂时返回空数组
        return [];
    }

    /**
     * 获取月度趋势数据
     * @return array<string, array<string, mixed>>
     */
    private function getMonthlyTrends(): array
    {
        $trends = [];
        $months = 6; // 最近6个月

        for ($i = $months - 1; $i >= 0; --$i) {
            $date = new \DateTime(sprintf('-%d months', $i));
            $monthKey = $date->format('Y-m');

            $trends[$monthKey] = [
                // 暂时注释掉不存在的方法调用
                // 'new_courses' => $this->courseRepository->countByMonth($date),
                // 'new_collects' => $this->collectRepository->countByMonth($date),
                // 'new_evaluates' => $this->evaluateRepository->countByMonth($date),
            ];
        }

        return $trends;
    }

    /**
     * 计算参与度
     * @param array<string, mixed> $collectStats
     * @param array<string, mixed> $evaluateStats
     */
    private function calculateEngagementRate(array $collectStats, array $evaluateStats): float
    {
        $totalCourses = $this->courseRepository->count([]);
        if (0 === $totalCourses) {
            return 0;
        }

        $engagedCourses = $this->countEngagedCourses($collectStats, $evaluateStats);

        return round($engagedCourses / $totalCourses * 100, 2);
    }

    /**
     * 统计参与的课程数量
     * @param array<string, mixed> $collectStats
     * @param array<string, mixed> $evaluateStats
     */
    private function countEngagedCourses(array $collectStats, array $evaluateStats): int
    {
        $collectCourseIds = $this->extractCourseIds($collectStats, 'by_course');
        $evaluateCourseIds = $this->extractCourseIds($evaluateStats, 'by_course');

        return count(array_unique(array_merge($collectCourseIds, $evaluateCourseIds)));
    }

    /**
     * 提取课程ID列表
     * @param array<string, mixed> $stats
     * @return array<int, mixed>
     */
    private function extractCourseIds(array $stats, string $key): array
    {
        $byCourse = $stats[$key] ?? [];
        if (!is_array($byCourse)) {
            return [];
        }

        return array_column($byCourse, 'course_id');
    }
}
