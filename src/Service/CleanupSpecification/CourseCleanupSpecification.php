<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CleanupSpecification;

use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程清理规范类
 *
 * 判断课程是否应该被清理的规则集合
 */
class CourseCleanupSpecification
{
    public function __construct(
        private readonly CourseConfigService $configService,
    ) {
    }

    /**
     * 判断课程是否应该被清理
     */
    public function shouldCleanupCourse(object $course): bool
    {
        return $this->isCourseInvalid($course)
            && $this->hasNoEngagement($course)
            && $this->isBeyondGracePeriod($course);
    }

    /**
     * 检查课程是否无效
     */
    private function isCourseInvalid(object $course): bool
    {
        return !method_exists($course, 'isValid') || !$course->isValid();
    }

    /**
     * 检查是否有学习参与度
     */
    private function hasNoEngagement(object $course): bool
    {
        if (!method_exists($course, 'getCollects') || !method_exists($course, 'getEvaluates')) {
            return true; // 如果无法检查，默认没有参与度
        }

        $collects = $course->getCollects();
        $evaluates = $course->getEvaluates();

        $collectsCount = is_countable($collects) ? count($collects) : 0;
        $evaluatesCount = is_countable($evaluates) ? count($evaluates) : 0;

        return 0 === $collectsCount && 0 === $evaluatesCount;
    }

    /**
     * 检查是否超过宽限期
     */
    private function isBeyondGracePeriod(object $course): bool
    {
        $gracePeriod = $this->configService->get('course.cleanup_grace_period_days', 7);
        if (!is_int($gracePeriod)) {
            $gracePeriod = 7;
        }

        $graceDate = new \DateTime(sprintf('-%d days', $gracePeriod));

        if (!method_exists($course, 'getCreateTime')) {
            return true; // 如果无法检查创建时间，默认超过宽限期
        }

        $createTime = $course->getCreateTime();

        return $createTime <= $graceDate;
    }
}
