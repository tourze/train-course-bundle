<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CourseContent;

use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课程内容工厂
 */
class CourseContentFactory
{
    /**
     * 创建课程章节
     * @param array<string, mixed> $data
     */
    public function createChapter(Course $course, array $data): Chapter
    {
        $title = is_string($data['title'] ?? null) ? $data['title'] : '';
        $sortNumber = is_int($data['sort_number'] ?? null) ? $data['sort_number'] : 0;

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle($title);
        // Chapter doesn't have description field
        $chapter->setSortNumber($sortNumber);

        return $chapter;
    }

    /**
     * 创建课程课时
     * @param array<string, mixed> $data
     */
    public function createLesson(Chapter $chapter, array $data): Lesson
    {
        $title = is_string($data['title'] ?? null) ? $data['title'] : '';
        $videoUrl = isset($data['video_url']) && is_string($data['video_url']) ? $data['video_url'] : null;
        $durationSecond = is_int($data['duration_second'] ?? null) ? $data['duration_second'] : 0;
        $sortNumber = is_int($data['sort_number'] ?? null) ? $data['sort_number'] : 0;

        $lesson = new Lesson();
        $lesson->setChapter($chapter);
        $lesson->setTitle($title);
        // Lesson doesn't have description field
        $lesson->setVideoUrl($videoUrl);
        $lesson->setDurationSecond($durationSecond);
        $lesson->setSortNumber($sortNumber);
        // Lesson doesn't have free field

        return $lesson;
    }

    /**
     * 创建课程大纲
     * @param array<string, mixed> $data
     */
    public function createOutline(Course $course, array $data): CourseOutline
    {
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setTitle($this->extractStringValue($data, 'title', ''));
        $outline->setLearningObjectives($this->extractNullableStringValue($data, 'learning_objectives'));
        $outline->setContentPoints($this->extractNullableStringValue($data, 'content_points'));
        $outline->setKeyDifficulties($this->extractNullableStringValue($data, 'key_difficulties'));
        $outline->setAssessmentCriteria($this->extractNullableStringValue($data, 'assessment_criteria'));
        $outline->setReferences($this->extractNullableStringValue($data, 'references'));
        $outline->setEstimatedMinutes($this->extractNullableIntValue($data, 'estimated_minutes'));
        $outline->setSortNumber($this->extractIntValue($data, 'sort_number', 0));
        $outline->setStatus($this->extractStringValue($data, 'status', 'draft'));

        return $outline;
    }

    /**
     * 从数组中提取字符串值
     * @param array<string, mixed> $data
     */
    private function extractStringValue(array $data, string $key, string $default): string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : $default;
    }

    /**
     * 从数组中提取可空字符串值
     * @param array<string, mixed> $data
     */
    private function extractNullableStringValue(array $data, string $key): ?string
    {
        return isset($data[$key]) && is_string($data[$key]) ? $data[$key] : null;
    }

    /**
     * 从数组中提取整数值
     * @param array<string, mixed> $data
     */
    private function extractIntValue(array $data, string $key, int $default): int
    {
        return is_int($data[$key] ?? null) ? $data[$key] : $default;
    }

    /**
     * 从数组中提取可空整数值
     * @param array<string, mixed> $data
     */
    private function extractNullableIntValue(array $data, string $key): ?int
    {
        return isset($data[$key]) && is_int($data[$key]) ? $data[$key] : null;
    }
}
