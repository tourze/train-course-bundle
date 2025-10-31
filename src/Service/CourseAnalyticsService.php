<?php

namespace Tourze\TrainCourseBundle\Service;

use Psr\Cache\CacheItemPoolInterface;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\CollectRepository;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;

/**
 * 课程分析服务
 *
 * 提供课程数据分析和统计功能，包括学习统计、评价分析、收藏趋势等
 */
class CourseAnalyticsService
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly CollectRepository $collectRepository,
        private readonly EvaluateRepository $evaluateRepository,
        private readonly CourseAuditRepository $auditRepository,
        private readonly CourseVersionRepository $versionRepository,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * 获取课程综合分析报告
     * @return array<string, mixed>
     */
    public function getCourseAnalyticsReport(Course $course): array
    {
        $cacheKey = sprintf('course_analytics_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            if (is_array($cached) && $this->isValidAnalyticsReport($cached)) {
                /** @var array<string, mixed> $cached */
                return $cached;
            }
        }

        /** @var array<string, mixed> $report */
        $report = [
            'course_info' => $this->getCourseBasicInfo($course),
            'popularity_metrics' => $this->getPopularityMetrics($course),
            'quality_metrics' => $this->getQualityMetrics($course),
            'engagement_metrics' => $this->getEngagementMetrics($course),
            'version_metrics' => $this->getVersionMetrics($course),
            'audit_metrics' => $this->getAuditMetrics($course),
            'recommendations' => $this->generateRecommendations($course),
        ];

        // 缓存2小时
        $cacheItem->set($report);
        $cacheItem->expiresAfter(7200);
        $this->cache->save($cacheItem);

        return $report;
    }

    /**
     * 获取课程基础信息
     */
    /**
     * @return array<string, mixed>
     */
    private function getCourseBasicInfo(Course $course): array
    {
        return [
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'category' => $course->getCategory()->getName(),
            'teacher_name' => $course->getTeacherName(),
            'price' => $course->getPrice(),
            'valid_day' => $course->getValidDay(),
            'learn_hour' => $course->getLearnHour(),
            'chapter_count' => $course->getChapterCount(),
            'lesson_count' => $course->getLessonCount(),
            'lesson_time' => $course->getLessonTime(),
            'duration_second' => $course->getDurationSecond(),
            'created_at' => $course->getCreateTime()?->format('Y-m-d H:i:s'),
            'is_valid' => $course->isValid(),
        ];
    }

    /**
     * 获取受欢迎程度指标
     */
    /**
     * @return array<string, mixed>
     */
    private function getPopularityMetrics(Course $course): array
    {
        $collectStats = $this->collectRepository->getCollectStatistics(null, $course);
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);

        return [
            'collect_count' => $collectStats['total_collects'],
            'evaluate_count' => $evaluateStats['total_evaluates'],
            'average_rating' => $evaluateStats['average_rating'],
            'rating_distribution' => $evaluateStats['rating_distribution'],
            'popularity_score' => $this->calculatePopularityScore($course, $collectStats, $evaluateStats),
        ];
    }

    /**
     * 获取质量指标
     */
    /**
     * @return array<string, mixed>
     */
    private function getQualityMetrics(Course $course): array
    {
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);
        $contentCompleteness = $this->calculateContentCompleteness($course);

        return [
            'content_completeness' => $contentCompleteness,
            'average_rating' => $evaluateStats['average_rating'],
            'high_rating_percentage' => $this->calculateHighRatingPercentage($evaluateStats),
            'quality_score' => $this->calculateQualityScore($course, $evaluateStats, $contentCompleteness),
        ];
    }

    /**
     * 获取参与度指标
     */
    /**
     * @return array<string, mixed>
     */
    private function getEngagementMetrics(Course $course): array
    {
        $collectStats = $this->collectRepository->getCollectStatistics(null, $course);
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);

        // 这里可以添加更多参与度指标，如学习进度、完成率等
        return [
            'collect_rate' => $this->calculateCollectRate($course, $collectStats),
            'evaluate_rate' => $this->calculateEvaluateRate($course, $evaluateStats),
            'engagement_score' => $this->calculateEngagementScore($course, $collectStats, $evaluateStats),
        ];
    }

    /**
     * 获取版本指标
     */
    /**
     * @return array<string, mixed>
     */
    private function getVersionMetrics(Course $course): array
    {
        $versionStats = $this->versionRepository->getVersionStatistics($course);
        $currentVersion = $this->versionRepository->findCurrentByCourse($course);

        return [
            'total_versions' => $versionStats['total_versions'],
            'published_versions' => $versionStats['published_versions'],
            'draft_versions' => $versionStats['draft_versions'],
            'current_version' => $currentVersion?->getVersion(),
            'version_activity' => $this->calculateVersionActivity($course),
        ];
    }

    /**
     * 获取审核指标
     */
    /**
     * @return array<string, mixed>
     */
    private function getAuditMetrics(Course $course): array
    {
        $auditStats = $this->auditRepository->getAuditStatistics($course);
        $latestAudit = $this->auditRepository->findLatestByCourse($course);

        return [
            'total_audits' => $auditStats['total_audits'],
            'pending_audits' => $auditStats['pending_audits'],
            'approved_audits' => $auditStats['approved_audits'],
            'rejected_audits' => $auditStats['rejected_audits'],
            'approval_rate' => $auditStats['approval_rate'],
            'latest_audit_status' => $latestAudit?->getStatus(),
            'latest_audit_time' => $latestAudit?->getAuditTime()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 生成改进建议
     * @return array<int, array<string, mixed>>
     */
    private function generateRecommendations(Course $course): array
    {
        $collectStats = $this->collectRepository->getCollectStatistics(null, $course);
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);
        $contentCompleteness = $this->calculateContentCompleteness($course);

        $recommendations = [];

        $contentRec = $this->buildContentCompletenessRecommendation($contentCompleteness);
        if (null !== $contentRec) {
            $recommendations[] = $contentRec;
        }

        $evaluationRec = $this->buildEvaluationRecommendation($evaluateStats);
        if (null !== $evaluationRec) {
            $recommendations[] = $evaluationRec;
        }

        $ratingRec = $this->buildRatingRecommendation($evaluateStats);
        if (null !== $ratingRec) {
            $recommendations[] = $ratingRec;
        }

        $collectionRec = $this->buildCollectionRecommendation($collectStats);
        if (null !== $collectionRec) {
            $recommendations[] = $collectionRec;
        }

        return $recommendations;
    }

    /**
     * 构建内容完整度建议
     * @param array<string, mixed> $contentCompleteness
     * @return array<string, mixed>|null
     */
    private function buildContentCompletenessRecommendation(array $contentCompleteness): ?array
    {
        if ($this->getFloatValue($contentCompleteness, 'percentage') >= 80) {
            return null;
        }

        return [
            'type' => 'content',
            'priority' => 'high',
            'message' => '课程内容完整度较低，建议完善课程大纲、章节和视频内容',
            'action' => 'improve_content',
        ];
    }

    /**
     * 构建评价建议
     * @param array<string, mixed> $evaluateStats
     * @return array<string, mixed>|null
     */
    private function buildEvaluationRecommendation(array $evaluateStats): ?array
    {
        if ($this->getIntValue($evaluateStats, 'total_evaluates') >= 5) {
            return null;
        }

        return [
            'type' => 'engagement',
            'priority' => 'medium',
            'message' => '课程评价数量较少，建议鼓励学员进行评价',
            'action' => 'encourage_evaluation',
        ];
    }

    /**
     * 构建评分建议
     * @param array<string, mixed> $evaluateStats
     * @return array<string, mixed>|null
     */
    private function buildRatingRecommendation(array $evaluateStats): ?array
    {
        $averageRating = $this->getFloatValue($evaluateStats, 'average_rating');
        $totalEvaluates = $this->getIntValue($evaluateStats, 'total_evaluates');

        if ($averageRating >= 4.0 || 0 === $totalEvaluates) {
            return null;
        }

        return [
            'type' => 'quality',
            'priority' => 'high',
            'message' => '课程平均评分较低，建议改进课程质量',
            'action' => 'improve_quality',
        ];
    }

    /**
     * 构建收藏建议
     * @param array<string, mixed> $collectStats
     * @return array<string, mixed>|null
     */
    private function buildCollectionRecommendation(array $collectStats): ?array
    {
        if ($this->getIntValue($collectStats, 'total_collects') >= 10) {
            return null;
        }

        return [
            'type' => 'popularity',
            'priority' => 'medium',
            'message' => '课程收藏数量较少，建议优化课程介绍和封面',
            'action' => 'improve_presentation',
        ];
    }

    /**
     * 从数组中安全获取整数值
     * @param array<int|string, mixed> $data
     * @param int|string $key
     */
    private function getIntValue(array $data, int|string $key): int
    {
        return is_int($data[$key] ?? null) ? $data[$key] : 0;
    }

    /**
     * 从数组中安全获取浮点数值
     * @param array<int|string, mixed> $data
     * @param int|string $key
     */
    private function getFloatValue(array $data, int|string $key): float
    {
        $value = $data[$key] ?? 0.0;

        return is_float($value) || is_int($value) ? (float) $value : 0.0;
    }

    /**
     * 从数组中安全获取数组值
     * @param array<int|string, mixed> $data
     * @param int|string $key
     * @return array<int|string, mixed>
     */
    private function getArrayValue(array $data, int|string $key): array
    {
        return is_array($data[$key] ?? null) ? $data[$key] : [];
    }

    /**
     * 计算受欢迎程度分数
     * @param array<string, mixed> $collectStats
     * @param array<string, mixed> $evaluateStats
     */
    private function calculatePopularityScore(Course $course, array $collectStats, array $evaluateStats): float
    {
        $totalCollects = $this->getIntValue($collectStats, 'total_collects');
        $totalEvaluates = $this->getIntValue($evaluateStats, 'total_evaluates');
        $averageRating = $this->getFloatValue($evaluateStats, 'average_rating');

        $collectScore = min(50, $totalCollects * 2);
        $evaluateScore = min(30, $totalEvaluates * 3);
        $ratingScore = $averageRating * 4;

        return round($collectScore + $evaluateScore + $ratingScore, 2);
    }

    /**
     * 计算质量分数
     * @param array<string, mixed> $evaluateStats
     * @param array<string, mixed> $contentCompleteness
     */
    private function calculateQualityScore(Course $course, array $evaluateStats, array $contentCompleteness): float
    {
        $percentage = $this->getFloatValue($contentCompleteness, 'percentage');
        $averageRating = $this->getFloatValue($evaluateStats, 'average_rating');

        $contentScore = $percentage * 0.4;
        $ratingScore = $averageRating * 20;
        $consistencyScore = $this->calculateRatingConsistency($evaluateStats) * 20;

        return round($contentScore + $ratingScore + $consistencyScore, 2);
    }

    /**
     * 计算参与度分数
     * @param array<string, mixed> $collectStats
     * @param array<string, mixed> $evaluateStats
     */
    private function calculateEngagementScore(Course $course, array $collectStats, array $evaluateStats): float
    {
        $totalCollects = $this->getIntValue($collectStats, 'total_collects');
        $totalEvaluates = $this->getIntValue($evaluateStats, 'total_evaluates');

        // 这里可以根据实际的学习数据来计算
        // 目前基于收藏和评价数据进行估算
        $collectEngagement = min(40, $totalCollects * 4);
        $evaluateEngagement = min(40, $totalEvaluates * 4);
        $interactionBonus = ($totalCollects > 0 && $totalEvaluates > 0) ? 20 : 0;

        return round($collectEngagement + $evaluateEngagement + $interactionBonus, 2);
    }

    /**
     * 计算内容完整度
     */
    /**
     * @return array<string, mixed>
     */
    private function calculateContentCompleteness(Course $course): array
    {
        $basicInfoScore = $this->calculateBasicInfoScore($course);
        $contentStructureScore = $this->calculateContentStructureScore($course);
        $detailsScore = $this->calculateDetailsScore($course);

        $totalScore = $basicInfoScore + $contentStructureScore + $detailsScore;
        $maxScore = 100;

        return [
            'score' => min($maxScore, $totalScore),
            'percentage' => min(100, round($totalScore / $maxScore * 100, 2)),
        ];
    }

    /**
     * 计算基础信息得分 (30分)
     */
    private function calculateBasicInfoScore(Course $course): int
    {
        $score = 0;

        if ('' !== $course->getTitle()) {
            $score += 8;
        }
        if (null !== $course->getDescription()) {
            $score += 8;
        }
        if (null !== $course->getCoverThumb()) {
            $score += 7;
        }
        if (null !== $course->getLearnHour()) {
            $score += 7;
        }

        return $score;
    }

    /**
     * 计算内容结构得分 (40分)
     */
    private function calculateContentStructureScore(Course $course): int
    {
        $score = 0;

        if ($course->getChapterCount() > 0) {
            $score += 20;
        }
        if ($course->getLessonCount() > 0) {
            $score += 20;
        }

        return $score;
    }

    /**
     * 计算详情得分 (30分)
     */
    private function calculateDetailsScore(Course $course): int
    {
        $score = 0;

        if ($course->getOutlineCount() > 0) {
            $score += 15;
        }
        if (null !== $course->getTeacherName()) {
            $score += 8;
        }
        if (null !== $course->getPrice()) {
            $score += 7;
        }

        return $score;
    }

    /**
     * 计算高评分百分比
     * @param array<string, mixed> $evaluateStats
     */
    private function calculateHighRatingPercentage(array $evaluateStats): float
    {
        $totalEvaluates = $this->getIntValue($evaluateStats, 'total_evaluates');

        if (0 === $totalEvaluates) {
            return 0.0;
        }

        $ratingDistribution = $this->getArrayValue($evaluateStats, 'rating_distribution');
        $rating4 = $this->getIntValue($ratingDistribution, 4);
        $rating5 = $this->getIntValue($ratingDistribution, 5);
        $highRatingCount = $rating4 + $rating5;

        return round($highRatingCount / $totalEvaluates * 100, 2);
    }

    /**
     * 计算评分一致性
     * @param array<string, mixed> $evaluateStats
     */
    private function calculateRatingConsistency(array $evaluateStats): float
    {
        $totalEvaluates = $this->getIntValue($evaluateStats, 'total_evaluates');

        if ($totalEvaluates < 2) {
            return 1.0;
        }

        // 计算评分分布的标准差，越小说明一致性越好
        $distribution = $this->getArrayValue($evaluateStats, 'rating_distribution');
        $mean = $this->getFloatValue($evaluateStats, 'average_rating');
        $variance = $this->calculateRatingVariance($distribution, $mean, $totalEvaluates);
        $stdDev = sqrt($variance);

        // 标准差越小，一致性越高（最大为1）
        return max(0.0, 1.0 - ($stdDev / 2));
    }

    /**
     * 计算评分方差
     * @param array<int|string, mixed> $distribution
     */
    private function calculateRatingVariance(array $distribution, float $mean, int $totalEvaluates): float
    {
        $variance = 0.0;

        for ($rating = 1; $rating <= 5; ++$rating) {
            $count = $this->getIntValue($distribution, $rating);
            $variance += $count * pow($rating - $mean, 2);
        }

        return $variance / $totalEvaluates;
    }

    /**
     * 计算收藏率
     * @param array<string, mixed> $collectStats
     */
    private function calculateCollectRate(Course $course, array $collectStats): float
    {
        $totalCollects = $this->getIntValue($collectStats, 'total_collects');

        // 这里需要实际的学习人数数据，目前使用估算
        $estimatedLearners = max(1, $totalCollects * 3);

        return round($totalCollects / $estimatedLearners * 100, 2);
    }

    /**
     * 计算评价率
     * @param array<string, mixed> $evaluateStats
     */
    private function calculateEvaluateRate(Course $course, array $evaluateStats): float
    {
        $totalEvaluates = $this->getIntValue($evaluateStats, 'total_evaluates');

        // 这里需要实际的学习人数数据，目前使用估算
        $estimatedLearners = max(1, $totalEvaluates * 5);

        return round($totalEvaluates / $estimatedLearners * 100, 2);
    }

    /**
     * 计算版本活跃度
     */
    private function calculateVersionActivity(Course $course): float
    {
        $versions = $this->versionRepository->findByCourse($course);

        if (count($versions) < 2) {
            return 0;
        }

        // 计算最近30天的版本更新频率
        $recentVersions = array_filter($versions, function ($version) {
            return $version->getCreateTime() > new \DateTime('-30 days');
        });

        return count($recentVersions);
    }

    /**
     * 获取课程排行榜
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    public function getCourseRankings(array $criteria = []): array
    {
        $cacheKey = 'course_rankings_' . md5(serialize($criteria));
        $cached = $this->getCachedRankings($cacheKey);

        if (null !== $cached) {
            return $cached;
        }

        $courses = $this->courseRepository->findValidCourses();
        $rankings = $this->buildRankingsData($courses);
        $rankings = $this->sortRankings($rankings, $criteria);
        $rankings = $this->limitRankings($rankings, $criteria);

        $this->cacheRankings($cacheKey, $rankings);

        return $rankings;
    }

    /**
     * 从缓存获取排行榜
     * @return array<int, array<string, mixed>>|null
     */
    private function getCachedRankings(string $cacheKey): ?array
    {
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            if (is_array($cached) && $this->isValidRankingsArray($cached)) {
                /** @var array<int, array<string, mixed>> $cached */
                return $cached;
            }
        }

        return null;
    }

    /**
     * 构建排行榜数据
     * @param array<int, Course> $courses
     * @return array<int, array<string, mixed>>
     */
    private function buildRankingsData(array $courses): array
    {
        $rankings = [];

        foreach ($courses as $course) {
            $rankings[] = $this->buildCourseRankingData($course);
        }

        return $rankings;
    }

    /**
     * 构建单个课程的排行榜数据
     * @return array<string, mixed>
     */
    private function buildCourseRankingData(Course $course): array
    {
        $collectStats = $this->collectRepository->getCollectStatistics(null, $course);
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);
        $contentCompleteness = $this->calculateContentCompleteness($course);

        return [
            'course' => $course,
            'popularity_score' => $this->calculatePopularityScore($course, $collectStats, $evaluateStats),
            'quality_score' => $this->calculateQualityScore($course, $evaluateStats, $contentCompleteness),
            'collect_count' => $collectStats['total_collects'],
            'evaluate_count' => $evaluateStats['total_evaluates'],
            'average_rating' => $evaluateStats['average_rating'],
        ];
    }

    /**
     * 排序排行榜
     * @param array<int, array<string, mixed>> $rankings
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    private function sortRankings(array $rankings, array $criteria): array
    {
        $sortBy = is_string($criteria['sort_by'] ?? null) ? $criteria['sort_by'] : 'popularity_score';

        usort($rankings, function (array $a, array $b) use ($sortBy): int {
            $aValue = $a[$sortBy] ?? 0;
            $bValue = $b[$sortBy] ?? 0;

            return $bValue <=> $aValue;
        });

        return $rankings;
    }

    /**
     * 限制排行榜数量
     * @param array<int, array<string, mixed>> $rankings
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    private function limitRankings(array $rankings, array $criteria): array
    {
        $limit = is_int($criteria['limit'] ?? null) ? $criteria['limit'] : 20;

        return array_slice($rankings, 0, $limit);
    }

    /**
     * 缓存排行榜数据
     * @param array<int, array<string, mixed>> $rankings
     */
    private function cacheRankings(string $cacheKey, array $rankings): void
    {
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set($rankings);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);
    }

    /**
     * 验证分析报告数据结构
     * @param mixed $data
     * @return bool
     */
    private function isValidAnalyticsReport($data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        $requiredKeys = [
            'course_info',
            'popularity_metrics',
            'quality_metrics',
            'engagement_metrics',
            'version_metrics',
            'audit_metrics',
            'recommendations',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 验证排行榜数据结构
     * @param mixed $data
     * @return bool
     */
    private function isValidRankingsArray($data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        // 检查是否是列表数组（整数键从0开始）
        $keys = array_keys($data);
        if ([] === $keys) {
            return true;
        }
        $firstKey = $keys[0];

        // 检查第一个元素的结构
        $first = reset($data);
        if (!is_array($first)) {
            return false;
        }

        $requiredKeys = [
            'course',
            'popularity_score',
            'quality_score',
            'collect_count',
            'evaluate_count',
            'average_rating',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $first)) {
                return false;
            }
        }

        return true;
    }
}
