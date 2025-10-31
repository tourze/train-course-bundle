<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

/**
 * 课程统计计算服务
 */
class CourseStatisticsCalculator
{
    /**
     * 计算课程统计信息
     * @param array<int, array<string, mixed>> $courseData
     * @return array{chapter_count: int, lesson_count: int}
     */
    public function calculateCourseStatistics(array $courseData): array
    {
        $chapterCount = 0;
        $lessonCount = 0;

        foreach ($courseData as $course) {
            $chapters = $course['chapters'] ?? [];
            if (is_countable($chapters)) {
                $chapterCount += count($chapters);
            }
            if (is_array($chapters)) {
                /** @var array<int, mixed> $chaptersArray */
                $chaptersArray = array_values($chapters);
                $lessonCount += $this->countLessonsInChapters($chaptersArray);
            }
        }

        return ['chapter_count' => $chapterCount, 'lesson_count' => $lessonCount];
    }

    /**
     * 统计章节中的课时数量
     * @param array<int, mixed> $chapters
     */
    private function countLessonsInChapters(array $chapters): int
    {
        $count = 0;
        foreach ($chapters as $chapter) {
            if (is_array($chapter) && isset($chapter['lessons']) && is_countable($chapter['lessons'])) {
                $count += count($chapter['lessons']);
            }
        }

        return $count;
    }
}
