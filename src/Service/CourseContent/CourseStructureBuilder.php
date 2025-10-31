<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CourseContent;

use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;

/**
 * 课程结构构建器
 */
class CourseStructureBuilder
{
    public function __construct(
        private readonly ChapterRepository $chapterRepository,
        private readonly CourseOutlineRepository $outlineRepository,
        private readonly CourseStatisticsCalculator $statisticsCalculator,
    ) {
    }

    /**
     * 构建课程完整内容结构
     * @return array<string, mixed>
     */
    public function buildCourseContentStructure(Course $course): array
    {
        $chapters = $this->chapterRepository->findByCourseWithLessons($course);
        $outlines = $this->outlineRepository->findPublishedByCourse($course);

        /** @var array<string, mixed> $structure */
        $structure = [
            'course' => $this->buildCourseInfo($course, $chapters),
            'chapters' => [],
            'outlines' => [],
            'statistics' => $this->statisticsCalculator->getCourseContentStatistics($course),
        ];

        // 组织章节和课时数据
        $structure['chapters'] = $this->buildChaptersList($chapters);

        // 组织大纲数据
        $structure['outlines'] = $this->buildOutlinesList($outlines);

        return $structure;
    }

    /**
     * 构建课程基本信息
     * @param array<Chapter> $chapters
     * @return array<string, mixed>
     */
    private function buildCourseInfo(Course $course, array $chapters): array
    {
        return [
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'description' => $course->getDescription(),
            'total_chapters' => count($chapters),
            'total_lessons' => $course->getLessonCount(),
            'total_duration' => $course->getDurationSecond(),
            'learn_hour' => $course->getLearnHour(),
        ];
    }

    /**
     * 构建章节列表数据
     * @param array<Chapter> $chapters
     * @return array<int, array<string, mixed>>
     */
    private function buildChaptersList(array $chapters): array
    {
        /** @var array<int, array<string, mixed>> $chaptersList */
        $chaptersList = [];

        foreach ($chapters as $chapter) {
            /** @var array<string, mixed> $chapterData */
            $chapterData = [
                'id' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                'sort_number' => $chapter->getSortNumber(),
                'lessons' => $this->buildLessonsList($chapter->getLessons()->toArray()),
            ];

            $chaptersList[] = $chapterData;
        }

        return $chaptersList;
    }

    /**
     * 构建课时列表数据
     * @param array<Lesson> $lessons
     * @return array<int, array<string, mixed>>
     */
    private function buildLessonsList(array $lessons): array
    {
        /** @var array<int, array<string, mixed>> $lessonsList */
        $lessonsList = [];

        foreach ($lessons as $lesson) {
            $lessonsList[] = [
                'id' => $lesson->getId(),
                'title' => $lesson->getTitle(),
                'video_url' => $lesson->getVideoUrl(),
                'duration_second' => $lesson->getDurationSecond(),
                'sort_number' => $lesson->getSortNumber(),
            ];
        }

        return $lessonsList;
    }

    /**
     * 构建大纲列表数据
     * @param array<CourseOutline> $outlines
     * @return array<int, array<string, mixed>>
     */
    private function buildOutlinesList(array $outlines): array
    {
        /** @var array<int, array<string, mixed>> $outlinesList */
        $outlinesList = [];

        foreach ($outlines as $outline) {
            $outlinesList[] = [
                'id' => $outline->getId(),
                'title' => $outline->getTitle(),
                'learning_objectives' => $outline->getLearningObjectives(),
                'content_points' => $outline->getContentPoints(),
                'key_difficulties' => $outline->getKeyDifficulties(),
                'assessment_criteria' => $outline->getAssessmentCriteria(),
                'estimated_minutes' => $outline->getEstimatedMinutes(),
                'sort_number' => $outline->getSortNumber(),
            ];
        }

        return $outlinesList;
    }

    /**
     * 构建课程统计信息
     * @return array<string, mixed>
     */
    public function buildStatistics(Course $course): array
    {
        $chapters = $course->getChapters();
        $totalLessons = 0;
        $totalDuration = 0;
        $totalOutlines = 0;
        $totalEstimatedMinutes = 0;

        foreach ($chapters as $chapter) {
            $lessons = $chapter->getLessons();
            $totalLessons += count($lessons);

            foreach ($lessons as $lesson) {
                $totalDuration += (int) ($lesson->getDurationSecond() ?? 0);
            }
        }

        // 处理课程大纲（属于Course，不是Chapter）
        $outlines = $course->getOutlines();
        $totalOutlines = count($outlines);

        foreach ($outlines as $outline) {
            $totalEstimatedMinutes += (int) ($outline->getEstimatedMinutes() ?? 0);
        }

        return [
            'course_id' => $course->getId(),
            'course_title' => $course->getTitle(),
            'total_chapters' => count($chapters),
            'total_lessons' => $totalLessons,
            'total_outlines' => $totalOutlines,
            'total_duration_seconds' => $totalDuration,
            'total_estimated_minutes' => $totalEstimatedMinutes,
            'average_lesson_duration' => $totalLessons > 0 ? (int) ($totalDuration / $totalLessons) : 0,
            'completion_percentage' => (function() use ($course) {
                $statistics = $this->statisticsCalculator->getCourseContentStatistics($course);
                /** @var array{percentage: float|int|string} $completeness */
                $completeness = $statistics['content_completeness'] ?? ['percentage' => 0];
                $percentage = $completeness['percentage'];
                return is_numeric($percentage) ? (int) $percentage : 0;
            })(),
            'last_updated' => $course->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }
}
