<?php

namespace Tourze\TrainCourseBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\LessonRepository;

/**
 * 课程内容服务
 * 
 * 管理课程内容的组织、检索和处理，包括章节、课时、大纲等
 */
class CourseContentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseRepository $courseRepository,
        private ChapterRepository $chapterRepository,
        private LessonRepository $lessonRepository,
        private CourseOutlineRepository $outlineRepository,
        private CacheItemPoolInterface $cache,
        private CourseConfigService $configService
    ) {
    }

    /**
     * 获取课程完整内容结构
     */
    public function getCourseContentStructure(Course $course): array
    {
        $cacheKey = sprintf('course_content_structure_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $chapters = $this->chapterRepository->findByCourseWithLessons($course);
        $outlines = $this->outlineRepository->findPublishedByCourse($course);

        $structure = [
            'course' => [
                'id' => $course->getId(),
                'title' => $course->getTitle(),
                'description' => $course->getDescription(),
                'total_chapters' => count($chapters),
                'total_lessons' => $course->getLessonCount(),
                'total_duration' => $course->getDurationSecond(),
                'learn_hour' => $course->getLearnHour(),
            ],
            'chapters' => [],
            'outlines' => [],
            'statistics' => $this->getCourseContentStatistics($course),
        ];

        // 组织章节和课时数据
        foreach ($chapters as $chapter) {
            $chapterData = [
                'id' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                'description' => $chapter->getDescription(),
                'sort_number' => $chapter->getSortNumber(),
                'lessons' => [],
            ];

            foreach ($chapter->getLessons() as $lesson) {
                $chapterData['lessons'][] = [
                    'id' => $lesson->getId(),
                    'title' => $lesson->getTitle(),
                    'description' => $lesson->getDescription(),
                    'video_url' => $lesson->getVideoUrl(),
                    'duration_second' => $lesson->getDurationSecond(),
                    'sort_number' => $lesson->getSortNumber(),
                    'is_free' => $lesson->isFree(),
                ];
            }

            $structure['chapters'][] = $chapterData;
        }

        // 组织大纲数据
        foreach ($outlines as $outline) {
            $structure['outlines'][] = [
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

        // 缓存1小时
        $cacheItem->set($structure);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $structure;
    }

    /**
     * 获取课程内容统计信息
     */
    public function getCourseContentStatistics(Course $course): array
    {
        $chapterStats = $this->chapterRepository->getChapterStatistics($course);
        $lessonStats = $this->lessonRepository->getLessonStatistics($course);
        $outlineStats = $this->outlineRepository->getOutlineStatistics($course);

        return [
            'chapters' => $chapterStats,
            'lessons' => $lessonStats,
            'outlines' => $outlineStats,
            'content_completeness' => $this->calculateContentCompleteness($course),
        ];
    }

    /**
     * 创建课程章节
     */
    public function createChapter(Course $course, array $data): Chapter
    {
        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle($data['title']);
        $chapter->setDescription($data['description'] ?? null);
        $chapter->setSortNumber($data['sort_number'] ?? 0);

        $this->entityManager->persist($chapter);
        $this->entityManager->flush();

        $this->clearCourseContentCache($course);

        return $chapter;
    }

    /**
     * 创建课程课时
     */
    public function createLesson(Chapter $chapter, array $data): Lesson
    {
        $lesson = new Lesson();
        $lesson->setChapter($chapter);
        $lesson->setTitle($data['title']);
        $lesson->setDescription($data['description'] ?? null);
        $lesson->setVideoUrl($data['video_url'] ?? null);
        $lesson->setDurationSecond($data['duration_second'] ?? 0);
        $lesson->setSortNumber($data['sort_number'] ?? 0);
        $lesson->setFree($data['is_free'] ?? false);

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->clearCourseContentCache($chapter->getCourse());

        return $lesson;
    }

    /**
     * 创建课程大纲
     */
    public function createOutline(Course $course, array $data): CourseOutline
    {
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setTitle($data['title']);
        $outline->setLearningObjectives($data['learning_objectives'] ?? null);
        $outline->setContentPoints($data['content_points'] ?? null);
        $outline->setKeyDifficulties($data['key_difficulties'] ?? null);
        $outline->setAssessmentCriteria($data['assessment_criteria'] ?? null);
        $outline->setReferences($data['references'] ?? null);
        $outline->setEstimatedMinutes($data['estimated_minutes'] ?? null);
        $outline->setSortNumber($data['sort_number'] ?? 0);
        $outline->setStatus($data['status'] ?? 'draft');

        $this->entityManager->persist($outline);
        $this->entityManager->flush();

        $this->clearCourseContentCache($course);

        return $outline;
    }

    /**
     * 批量导入课程内容
     */
    public function batchImportContent(Course $course, array $contentData): array
    {
        $results = [
            'chapters' => [],
            'lessons' => [],
            'outlines' => [],
            'errors' => [],
        ];

        $this->entityManager->beginTransaction();

        try {
            // 导入章节
            if (isset($contentData['chapters'])) {
                foreach ($contentData['chapters'] as $chapterData) {
                    try {
                        $chapter = $this->createChapter($course, $chapterData);
                        $results['chapters'][] = $chapter->getId();

                        // 导入该章节的课时
                        if (isset($chapterData['lessons'])) {
                            foreach ($chapterData['lessons'] as $lessonData) {
                                try {
                                    $lesson = $this->createLesson($chapter, $lessonData);
                                    $results['lessons'][] = $lesson->getId();
                                } catch (\Exception $e) {
                                    $results['errors'][] = "课时导入失败: " . $e->getMessage();
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = "章节导入失败: " . $e->getMessage();
                    }
                }
            }

            // 导入大纲
            if (isset($contentData['outlines'])) {
                foreach ($contentData['outlines'] as $outlineData) {
                    try {
                        $outline = $this->createOutline($course, $outlineData);
                        $results['outlines'][] = $outline->getId();
                    } catch (\Exception $e) {
                        $results['errors'][] = "大纲导入失败: " . $e->getMessage();
                    }
                }
            }

            $this->entityManager->commit();
            $this->clearCourseContentCache($course);

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $results['errors'][] = "批量导入失败: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * 重新排序章节
     */
    public function reorderChapters(Course $course, array $chapterIds): bool
    {
        try {
            $this->entityManager->beginTransaction();

            foreach ($chapterIds as $index => $chapterId) {
                $chapter = $this->chapterRepository->find($chapterId);
                if ($chapter && $chapter->getCourse()->getId() === $course->getId()) {
                    $chapter->setSortNumber(count($chapterIds) - $index);
                    $this->entityManager->persist($chapter);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->clearCourseContentCache($course);
            return true;

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return false;
        }
    }

    /**
     * 重新排序课时
     */
    public function reorderLessons(Chapter $chapter, array $lessonIds): bool
    {
        try {
            $this->entityManager->beginTransaction();

            foreach ($lessonIds as $index => $lessonId) {
                $lesson = $this->lessonRepository->find($lessonId);
                if ($lesson && $lesson->getChapter()->getId() === $chapter->getId()) {
                    $lesson->setSortNumber(count($lessonIds) - $index);
                    $this->entityManager->persist($lesson);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->clearCourseContentCache($chapter->getCourse());
            return true;

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return false;
        }
    }

    /**
     * 计算内容完整度
     */
    private function calculateContentCompleteness(Course $course): array
    {
        $score = 0;
        $maxScore = 100;
        $details = [];

        // 基础信息完整度 (20分)
        if ($course->getTitle()) $score += 5;
        if ($course->getDescription()) $score += 5;
        if ($course->getCoverThumb()) $score += 5;
        if ($course->getLearnHour()) $score += 5;

        // 章节内容完整度 (40分)
        $chapters = $this->chapterRepository->findByCourse($course);
        if (count($chapters) > 0) {
            $score += 20;
            $lessonsCount = 0;
            foreach ($chapters as $chapter) {
                $lessonsCount += $chapter->getLessons()->count();
            }
            if ($lessonsCount > 0) $score += 20;
        }

        // 大纲完整度 (20分)
        $outlines = $this->outlineRepository->findByCourse($course);
        if (count($outlines) > 0) {
            $score += 10;
            $publishedOutlines = $this->outlineRepository->findPublishedByCourse($course);
            if (count($publishedOutlines) > 0) $score += 10;
        }

        // 视频内容完整度 (20分)
        $lessonsWithVideo = $this->lessonRepository->findLessonsWithVideo($course);
        if (count($lessonsWithVideo) > 0) {
            $score += 20;
        }

        $details = [
            'basic_info' => min(20, $score <= 20 ? $score : 20),
            'chapters_lessons' => min(40, max(0, $score - 20) <= 40 ? max(0, $score - 20) : 40),
            'outlines' => min(20, max(0, $score - 60) <= 20 ? max(0, $score - 60) : 20),
            'videos' => min(20, max(0, $score - 80) <= 20 ? max(0, $score - 80) : 20),
        ];

        return [
            'score' => min($maxScore, $score),
            'percentage' => min(100, round($score / $maxScore * 100, 2)),
            'details' => $details,
        ];
    }

    /**
     * 清除课程内容缓存
     */
    private function clearCourseContentCache(Course $course): void
    {
        $cacheKey = sprintf('course_content_structure_%s', $course->getId());
        $this->cache->deleteItem($cacheKey);
    }
} 