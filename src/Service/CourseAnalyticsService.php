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
     */
    public function getCourseAnalyticsReport(Course $course): array
    {
        $cacheKey = sprintf('course_analytics_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

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
    private function getCourseBasicInfo(Course $course): array
    {
        return [
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'category' => $course->getCategory()->getTitle(),
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
     */
    private function generateRecommendations(Course $course): array
    {
        $recommendations = [];
        
        $collectStats = $this->collectRepository->getCollectStatistics(null, $course);
        $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);
        $contentCompleteness = $this->calculateContentCompleteness($course);

        // 内容完整度建议
        if ($contentCompleteness['percentage'] < 80) {
            $recommendations[] = [
                'type' => 'content',
                'priority' => 'high',
                'message' => '课程内容完整度较低，建议完善课程大纲、章节和视频内容',
                'action' => 'improve_content',
            ];
        }

        // 评价建议
        if ($evaluateStats['total_evaluates'] < 5) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'message' => '课程评价数量较少，建议鼓励学员进行评价',
                'action' => 'encourage_evaluation',
            ];
        }

        // 评分建议
        if ($evaluateStats['average_rating'] < 4.0 && $evaluateStats['total_evaluates'] > 0) {
            $recommendations[] = [
                'type' => 'quality',
                'priority' => 'high',
                'message' => '课程平均评分较低，建议改进课程质量',
                'action' => 'improve_quality',
            ];
        }

        // 收藏建议
        if ($collectStats['total_collects'] < 10) {
            $recommendations[] = [
                'type' => 'popularity',
                'priority' => 'medium',
                'message' => '课程收藏数量较少，建议优化课程介绍和封面',
                'action' => 'improve_presentation',
            ];
        }

        return $recommendations;
    }

    /**
     * 计算受欢迎程度分数
     */
    private function calculatePopularityScore(Course $course, array $collectStats, array $evaluateStats): float
    {
        $collectScore = min(50, $collectStats['total_collects'] * 2);
        $evaluateScore = min(30, $evaluateStats['total_evaluates'] * 3);
        $ratingScore = $evaluateStats['average_rating'] * 4;

        return round($collectScore + $evaluateScore + $ratingScore, 2);
    }

    /**
     * 计算质量分数
     */
    private function calculateQualityScore(Course $course, array $evaluateStats, array $contentCompleteness): float
    {
        $contentScore = $contentCompleteness['percentage'] * 0.4;
        $ratingScore = $evaluateStats['average_rating'] * 20;
        $consistencyScore = $this->calculateRatingConsistency($evaluateStats) * 20;

        return round($contentScore + $ratingScore + $consistencyScore, 2);
    }

    /**
     * 计算参与度分数
     */
    private function calculateEngagementScore(Course $course, array $collectStats, array $evaluateStats): float
    {
        // 这里可以根据实际的学习数据来计算
        // 目前基于收藏和评价数据进行估算
        $collectEngagement = min(40, $collectStats['total_collects'] * 4);
        $evaluateEngagement = min(40, $evaluateStats['total_evaluates'] * 4);
        $interactionBonus = ($collectStats['total_collects'] > 0 && $evaluateStats['total_evaluates'] > 0) ? 20 : 0;

        return round($collectEngagement + $evaluateEngagement + $interactionBonus, 2);
    }

    /**
     * 计算内容完整度
     */
    private function calculateContentCompleteness(Course $course): array
    {
        $score = 0;
        $maxScore = 100;

        // 基础信息 (30分)
        if ('' !== $course->getTitle()) $score += 8;
        if (null !== $course->getDescription()) $score += 8;
        if (null !== $course->getCoverThumb()) $score += 7;
        if (null !== $course->getLearnHour()) $score += 7;

        // 内容结构 (40分)
        if ($course->getChapterCount() > 0) $score += 20;
        if ($course->getLessonCount() > 0) $score += 20;

        // 大纲和详情 (30分)
        if ($course->getOutlineCount() > 0) $score += 15;
        if (null !== $course->getTeacherName()) $score += 8;
        if (null !== $course->getPrice()) $score += 7;

        return [
            'score' => min($maxScore, $score),
            'percentage' => min(100, round($score / $maxScore * 100, 2)),
        ];
    }

    /**
     * 计算高评分百分比
     */
    private function calculateHighRatingPercentage(array $evaluateStats): float
    {
        if ($evaluateStats['total_evaluates'] == 0) {
            return 0;
        }

        $highRatingCount = ($evaluateStats['rating_distribution'][4] ?? 0) + 
                          ($evaluateStats['rating_distribution'][5] ?? 0);

        return round($highRatingCount / $evaluateStats['total_evaluates'] * 100, 2);
    }

    /**
     * 计算评分一致性
     */
    private function calculateRatingConsistency(array $evaluateStats): float
    {
        if ($evaluateStats['total_evaluates'] < 2) {
            return 1.0;
        }

        // 计算评分分布的标准差，越小说明一致性越好
        $distribution = $evaluateStats['rating_distribution'];
        $mean = $evaluateStats['average_rating'];
        $variance = 0;

        for ($rating = 1; $rating <= 5; $rating++) {
            $count = $distribution[$rating] ?? 0;
            $variance += $count * pow($rating - $mean, 2);
        }

        $variance /= $evaluateStats['total_evaluates'];
        $stdDev = sqrt($variance);

        // 标准差越小，一致性越高（最大为1）
        return max(0, 1 - ($stdDev / 2));
    }

    /**
     * 计算收藏率
     */
    private function calculateCollectRate(Course $course, array $collectStats): float
    {
        // 这里需要实际的学习人数数据，目前使用估算
        $estimatedLearners = max(1, $collectStats['total_collects'] * 3);
        return round($collectStats['total_collects'] / $estimatedLearners * 100, 2);
    }

    /**
     * 计算评价率
     */
    private function calculateEvaluateRate(Course $course, array $evaluateStats): float
    {
        // 这里需要实际的学习人数数据，目前使用估算
        $estimatedLearners = max(1, $evaluateStats['total_evaluates'] * 5);
        return round($evaluateStats['total_evaluates'] / $estimatedLearners * 100, 2);
    }

    /**
     * 计算版本活跃度
     */
    private function calculateVersionActivity(Course $course): float
    {
        $versions = $this->versionRepository->findByCourse($course);
        
        if ((bool) count($versions) < 2) {
            return 0;
        }

        // 计算最近30天的版本更新频率
        $recentVersions = array_filter($versions, function($version) {
            return $version->getCreateTime() > new \DateTime('-30 days');
        });

        return count($recentVersions);
    }

    /**
     * 获取课程排行榜
     */
    public function getCourseRankings(array $criteria = []): array
    {
        $cacheKey = 'course_rankings_' . md5(serialize($criteria));
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $courses = $this->courseRepository->findValidCourses();
        $rankings = [];

        foreach ($courses as $course) {
            $collectStats = $this->collectRepository->getCollectStatistics(null, $course);
            $evaluateStats = $this->evaluateRepository->getEvaluateStatistics($course);

            $rankings[] = [
                'course' => $course,
                'popularity_score' => $this->calculatePopularityScore($course, $collectStats, $evaluateStats),
                'quality_score' => $this->calculateQualityScore($course, $evaluateStats, $this->calculateContentCompleteness($course)),
                'collect_count' => $collectStats['total_collects'],
                'evaluate_count' => $evaluateStats['total_evaluates'],
                'average_rating' => $evaluateStats['average_rating'],
            ];
        }

        // 根据不同标准排序
        $sortBy = $criteria['sort_by'] ?? 'popularity_score';
        usort($rankings, function($a, $b) use ($sortBy) {
            return $b[$sortBy] <=> $a[$sortBy];
        });

        $limit = $criteria['limit'] ?? 20;
        $rankings = array_slice($rankings, 0, $limit);

        // 缓存1小时
        $cacheItem->set($rankings);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $rankings;
    }
} 