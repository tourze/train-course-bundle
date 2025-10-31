<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\LessonRepository;

/**
 * 课时服务
 *
 * 提供课时相关的业务逻辑处理
 */
#[Autoconfigure(public: true)]
class LessonService
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
    ) {
    }

    /**
     * 根据ID查找课时
     */
    public function findById(string $id): ?Lesson
    {
        return $this->lessonRepository->find($id);
    }

    /**
     * 根据条件查找课时
     *
     * @param array<string, mixed> $criteria
     * @param array<string, 'ASC'|'DESC'>|null $orderBy
     * @return Lesson[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->lessonRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * 根据条件查找单个课时
     *
     * @param array<string, mixed> $criteria
     * @param array<string, 'ASC'|'DESC'>|null $orderBy
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?Lesson
    {
        return $this->lessonRepository->findOneBy($criteria, $orderBy);
    }

    /**
     * 根据章节查找课时
     *
     * @return Lesson[]
     */
    public function findByChapter(Chapter $chapter): array
    {
        return $this->lessonRepository->findByChapter($chapter);
    }

    /**
     * 根据课程查找所有课时
     *
     * @return Lesson[]
     */
    public function findByCourse(Course $course): array
    {
        return $this->lessonRepository->findByCourse($course);
    }

    /**
     * 查找有视频的课时
     *
     * @return Lesson[]
     */
    public function findLessonsWithVideo(Chapter $chapter): array
    {
        return $this->lessonRepository->findLessonsWithVideo($chapter);
    }

    /**
     * 获取课时统计信息
     *
     * @return array<string, mixed>
     */
    public function getLessonStatistics(Chapter $chapter): array
    {
        return $this->lessonRepository->getLessonStatistics($chapter);
    }

    /**
     * 搜索课时
     *
     * @return Lesson[]
     */
    public function searchLessons(string $keyword, ?Chapter $chapter = null): array
    {
        return $this->lessonRepository->searchLessons($keyword, $chapter);
    }

    /**
     * 根据视频协议查找课时
     *
     * @return Lesson[]
     */
    public function findByVideoProtocol(string $protocol): array
    {
        return $this->lessonRepository->findByVideoProtocol($protocol);
    }

    /**
     * 检查课时是否有视频
     */
    public function hasVideo(Lesson $lesson): bool
    {
        $videoUrl = $lesson->getVideoUrl();

        return null !== $videoUrl && '' !== $videoUrl;
    }

    /**
     * 获取课时时长（秒）
     */
    public function getLessonDuration(Lesson $lesson): int
    {
        return $lesson->getDurationSecond() ?? 0;
    }

    /**
     * 验证课时是否属于指定课程
     */
    public function belongsToCourse(Lesson $lesson, Course $course): bool
    {
        $chapter = $lesson->getChapter();
        $lessonCourse = $chapter->getCourse();

        return $lessonCourse->getId() === $course->getId();
    }

    /**
     * 获取课时在章节中的位置
     */
    public function getPositionInChapter(Lesson $lesson): int
    {
        $chapter = $lesson->getChapter();

        $lessons = $this->findByChapter($chapter);
        foreach ($lessons as $index => $chapterLesson) {
            if ($chapterLesson->getId() === $lesson->getId()) {
                return is_int($index) ? $index + 1 : 1;
            }
        }

        return 0;
    }

    /**
     * 获取所有课时
     *
     * @return Lesson[]
     */
    public function findAll(): array
    {
        return $this->lessonRepository->findAll();
    }
}
