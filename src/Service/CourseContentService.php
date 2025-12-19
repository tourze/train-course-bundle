<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Service\CourseContent\ContentOrderManager;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseContentFactory;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseContentImporter;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseStructureBuilder;

/**
 * 课程内容服务
 *
 * 管理课程内容的组织、检索和处理，包括章节、课时、大纲等
 */
readonly class CourseContentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheItemPoolInterface $cache,
        private CourseStructureBuilder $structureBuilder,
        private CourseContentFactory $contentFactory,
        private CourseContentImporter $contentImporter,
        private ContentOrderManager $orderManager,
    ) {
    }

    /**
     * 获取课程完整内容结构
     * @return array<string, mixed>
     */
    public function getCourseContentStructure(Course $course): array
    {
        $cacheKey = sprintf('course_content_structure_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            if (is_array($cached)) {
                /** @var array<string, mixed> $cached */
                return $cached;
            }
        }

        $structure = $this->structureBuilder->buildCourseContentStructure($course);

        // 缓存1小时
        $cacheItem->set($structure);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $structure;
    }

    /**
     * 创建课程章节
     * @param array<string, mixed> $data
     */
    public function createChapter(Course $course, array $data): Chapter
    {
        $chapter = $this->contentFactory->createChapter($course, $data);

        $this->entityManager->persist($chapter);
        $this->entityManager->flush();

        $this->clearCourseContentCache($course);

        return $chapter;
    }

    /**
     * 创建课程课时
     * @param array<string, mixed> $data
     */
    public function createLesson(Chapter $chapter, array $data): Lesson
    {
        $lesson = $this->contentFactory->createLesson($chapter, $data);

        $this->entityManager->persist($lesson);
        $this->entityManager->flush();

        $this->clearCourseContentCache($chapter->getCourse());

        return $lesson;
    }

    /**
     * 创建课程大纲
     * @param array<string, mixed> $data
     */
    public function createOutline(Course $course, array $data): CourseOutline
    {
        $outline = $this->contentFactory->createOutline($course, $data);

        $this->entityManager->persist($outline);
        $this->entityManager->flush();

        $this->clearCourseContentCache($course);

        return $outline;
    }

    /**
     * 批量导入课程内容
     * @param array<string, mixed> $contentData
     * @return array<string, mixed>
     */
    public function batchImportContent(Course $course, array $contentData): array
    {
        $this->entityManager->beginTransaction();

        try {
            $results = $this->contentImporter->batchImportContent($course, $contentData);
            $this->entityManager->commit();
            $this->clearCourseContentCache($course);
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $results = ['errors' => ['批量导入失败: ' . $e->getMessage()]];
        }

        return $results;
    }

    /**
     * 重新排序章节
     * @param array<int, string> $chapterIds
     */
    public function reorderChapters(Course $course, array $chapterIds): bool
    {
        $result = $this->orderManager->reorderChapters($course, $chapterIds);

        if ($result) {
            $this->entityManager->flush();
            $this->clearCourseContentCache($course);
        }

        return $result;
    }

    /**
     * 重新排序课时
     * @param array<int, string> $lessonIds
     */
    public function reorderLessons(Chapter $chapter, array $lessonIds): bool
    {
        $result = $this->orderManager->reorderLessons($chapter, $lessonIds);

        if ($result) {
            $this->entityManager->flush();
            $this->clearCourseContentCache($chapter->getCourse());
        }

        return $result;
    }

    /**
     * 获取课程内容统计信息
     * @return array<string, mixed>
     */
    public function getCourseContentStatistics(Course $course): array
    {
        $cacheKey = sprintf('course_content_statistics_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            if (is_array($cached)) {
                /** @var array<string, mixed> $cached */
                return $cached;
            }
        }

        // 构建统计信息
        $statistics = $this->structureBuilder->buildStatistics($course);

        // 缓存结果
        $cacheItem->set($statistics);
        $cacheItem->expiresAfter(3600); // 缓存1小时
        $this->cache->save($cacheItem);

        return $statistics;
    }

    /**
     * 清除课程内容缓存
     */
    private function clearCourseContentCache(Course $course): void
    {
        $cacheKey = sprintf('course_content_structure_%s', $course->getId());
        $this->cache->deleteItem($cacheKey);

        $cacheKeyStats = sprintf('course_content_statistics_%s', $course->getId());
        $this->cache->deleteItem($cacheKeyStats);
    }
}
