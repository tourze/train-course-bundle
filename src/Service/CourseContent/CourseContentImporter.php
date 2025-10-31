<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CourseContent;

use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课程内容导入器
 */
class CourseContentImporter
{
    public function __construct(
        private readonly CourseContentFactory $factory,
    ) {
    }

    /**
     * 批量导入课程内容
     * @param array<string, mixed> $contentData
     * @return array<string, mixed>
     */
    public function batchImportContent(Course $course, array $contentData): array
    {
        $results = $this->initializeImportResults();

        try {
            $results = $this->executeBatchImport($course, $contentData, $results);
        } catch (\Throwable $e) {
            $results = $this->addImportError($results, '批量导入失败: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * 执行批量导入
     * @param array<string, mixed> $contentData
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function executeBatchImport(Course $course, array $contentData, array $results): array
    {
        $results = $this->importChaptersWithLessons($course, $contentData, $results);

        return $this->importOutlines($course, $contentData, $results);
    }

    /**
     * 添加导入错误
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function addImportError(array $results, string $message): array
    {
        if (is_array($results['errors'])) {
            $results['errors'][] = $message;
        }

        return $results;
    }

    /**
     * 导入章节和课时
     * @param array<string, mixed> $contentData
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function importChaptersWithLessons(Course $course, array $contentData, array $results): array
    {
        if (!isset($contentData['chapters']) || !is_array($contentData['chapters'])) {
            return $results;
        }

        foreach ($contentData['chapters'] as $chapterData) {
            if (!is_array($chapterData)) {
                continue;
            }

            try {
                /** @var array<string, mixed> $typedChapterData */
                $typedChapterData = $chapterData;
                $chapter = $this->factory->createChapter($course, $typedChapterData);
                if (is_array($results['chapters'])) {
                    $results['chapters'][] = $chapter->getId();
                }
                $results = $this->importLessonsForChapter($chapter, $typedChapterData, $results);
            } catch (\Throwable $e) {
                if (is_array($results['errors'])) {
                    $results['errors'][] = '章节导入失败: ' . $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * 为章节导入课时
     * @param array<string, mixed> $chapterData
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function importLessonsForChapter(Chapter $chapter, array $chapterData, array $results): array
    {
        if (!isset($chapterData['lessons']) || !is_array($chapterData['lessons'])) {
            return $results;
        }

        foreach ($chapterData['lessons'] as $lessonData) {
            if (!is_array($lessonData)) {
                continue;
            }

            try {
                /** @var array<string, mixed> $typedLessonData */
                $typedLessonData = $lessonData;
                $lesson = $this->factory->createLesson($chapter, $typedLessonData);
                if (is_array($results['lessons'])) {
                    $results['lessons'][] = $lesson->getId();
                }
            } catch (\Throwable $e) {
                if (is_array($results['errors'])) {
                    $results['errors'][] = '课时导入失败: ' . $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * 导入大纲
     * @param array<string, mixed> $contentData
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function importOutlines(Course $course, array $contentData, array $results): array
    {
        if (!isset($contentData['outlines']) || !is_array($contentData['outlines'])) {
            return $results;
        }

        foreach ($contentData['outlines'] as $outlineData) {
            if (!is_array($outlineData)) {
                continue;
            }

            try {
                /** @var array<string, mixed> $typedOutlineData */
                $typedOutlineData = $outlineData;
                $outline = $this->factory->createOutline($course, $typedOutlineData);
                if (is_array($results['outlines'])) {
                    $results['outlines'][] = $outline->getId();
                }
            } catch (\Throwable $e) {
                if (is_array($results['errors'])) {
                    $results['errors'][] = '大纲导入失败: ' . $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * 初始化导入结果
     * @return array<string, mixed>
     */
    private function initializeImportResults(): array
    {
        return [
            'chapters' => [],
            'lessons' => [],
            'outlines' => [],
            'errors' => [],
        ];
    }
}
