<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CourseContent;

use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;

/**
 * 课程统计计算器
 */
class CourseStatisticsCalculator
{
    public function __construct(
        private readonly ChapterRepository $chapterRepository,
        private readonly CourseOutlineRepository $outlineRepository,
        private readonly ContentCompletenessCalculator $completenessCalculator,
    ) {
    }

    /**
     * 获取课程内容统计信息
     * @return array<string, mixed>
     */
    public function getCourseContentStatistics(Course $course): array
    {
        $chapterStats = $this->chapterRepository->getChapterStatistics($course);
        // 需要修改，getLessonStatistics 期望 Chapter 参数而不是 Course
        $lessonStats = [];
        $outlineStats = $this->outlineRepository->getOutlineStatistics($course);

        return [
            'chapters' => $chapterStats,
            'lessons' => $lessonStats,
            'outlines' => $outlineStats,
            'content_completeness' => $this->completenessCalculator->calculateContentCompleteness($course),
        ];
    }
}
