<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CourseContent;

use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 内容排序管理器
 */
class ContentOrderManager
{
    /**
     * @param object $chapterRepository 需要实现相关章节查询方法
     * @param object $lessonRepository 需要实现相关课时查询方法
     */
    public function __construct(
        private readonly object $chapterRepository,
        private readonly object $lessonRepository,
    ) {
    }

    /**
     * 重新排序章节
     * @param array<int, string> $chapterIds
     */
    public function reorderChapters(Course $course, array $chapterIds): bool
    {
        try {
            $this->updateChapterOrder($course, $chapterIds);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 重新排序课时
     * @param array<int, string> $lessonIds
     */
    public function reorderLessons(Chapter $chapter, array $lessonIds): bool
    {
        try {
            $this->updateLessonOrder($chapter, $lessonIds);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 更新章节排序
     * @param array<int, string> $chapterIds
     */
    private function updateChapterOrder(Course $course, array $chapterIds): void
    {
        foreach ($chapterIds as $index => $chapterId) {
            $chapter = $this->chapterRepository->find($chapterId);
            if ($chapter instanceof Chapter && $chapter->getCourse()->getId() === $course->getId()) {
                $sortNumber = count($chapterIds) - $index;
                $chapter->setSortNumber($sortNumber);
            }
        }
    }

    /**
     * 更新课时排序
     * @param array<int, string> $lessonIds
     */
    private function updateLessonOrder(Chapter $chapter, array $lessonIds): void
    {
        foreach ($lessonIds as $index => $lessonId) {
            $lesson = $this->lessonRepository->find($lessonId);
            if ($lesson instanceof Lesson && $lesson->getChapter()->getId() === $chapter->getId()) {
                $sortNumber = count($lessonIds) - $index;
                $lesson->setSortNumber($sortNumber);
            }
        }
    }
}
