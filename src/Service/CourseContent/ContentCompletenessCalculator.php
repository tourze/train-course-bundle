<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CourseContent;

use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 内容完整度计算器
 */
class ContentCompletenessCalculator
{
    /**
     * @param object $chapterRepository 需要实现 findByCourse 方法
     * @param object $outlineRepository 需要实现 findByCourse 和 findPublishedByCourse 方法
     */
    public function __construct(
        private readonly object $chapterRepository,
        private readonly object $outlineRepository,
    ) {
    }

    /**
     * 计算内容完整度
     * @return array<string, mixed>
     */
    public function calculateContentCompleteness(Course $course): array
    {
        $maxScore = 100;

        $basicInfoScore = $this->calculateBasicContentScore($course);
        $chaptersLessonsScore = $this->calculateChaptersLessonsScore($course);
        $outlinesScore = $this->calculateOutlinesScore($course);

        $totalScore = $basicInfoScore + $chaptersLessonsScore + $outlinesScore;

        $details = [
            'basic_info' => $basicInfoScore,
            'chapters_lessons' => $chaptersLessonsScore,
            'outlines' => $outlinesScore,
            'videos' => 0, // 暂时跳过视频检查
        ];

        return [
            'score' => min($maxScore, $totalScore),
            'percentage' => min(100, round($totalScore / $maxScore * 100, 2)),
            'details' => $details,
        ];
    }

    /**
     * 计算基础内容得分 (20分)
     */
    private function calculateBasicContentScore(Course $course): int
    {
        $score = 0;

        if ('' !== $course->getTitle()) {
            $score += 5;
        }
        if (null !== $course->getDescription()) {
            $score += 5;
        }
        if (null !== $course->getCoverThumb()) {
            $score += 5;
        }
        if (null !== $course->getLearnHour()) {
            $score += 5;
        }

        return $score;
    }

    /**
     * 计算章节课时得分 (40分)
     */
    private function calculateChaptersLessonsScore(Course $course): int
    {
        $score = 0;
        $chapters = $this->chapterRepository->findByCourse($course);

        if (count($chapters) > 0) {
            $score += 20;
            $lessonsCount = 0;
            foreach ($chapters as $chapter) {
                $lessonsCount += $chapter->getLessons()->count();
            }
            if ($lessonsCount > 0) {
                $score += 20;
            }
        }

        return $score;
    }

    /**
     * 计算大纲得分 (20分)
     */
    private function calculateOutlinesScore(Course $course): int
    {
        $score = 0;
        $outlines = $this->outlineRepository->findByCourse($course);

        if (count($outlines) > 0) {
            $score += 10;
            $publishedOutlines = $this->outlineRepository->findPublishedByCourse($course);
            if (count($publishedOutlines) > 0) {
                $score += 10;
            }
        }

        return $score;
    }
}
