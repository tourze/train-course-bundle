<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service;

use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课时服务接口
 */
interface LessonServiceInterface
{
    /**
     * 根据ID查找课时
     */
    public function findById(string $id): ?Lesson;

    /**
     * 根据条件查找单个课时
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?Lesson;

    /**
     * 根据条件查找多个课时
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return Lesson[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * 根据章节查找课时
     *
     * @return Lesson[]
     */
    public function findByChapter(Chapter $chapter): array;

    /**
     * 根据课程查找所有课时
     *
     * @return Lesson[]
     */
    public function findByCourse(Course $course): array;

    /**
     * 查找有视频的课时
     *
     * @return Lesson[]
     */
    public function findLessonsWithVideo(Chapter $chapter): array;

    /**
     * 获取课时统计信息
     *
     * @return array<string, mixed>
     */
    public function getLessonStatistics(Chapter $chapter): array;

    /**
     * 搜索课时
     *
     * @return Lesson[]
     */
    public function searchLessons(string $keyword, ?Chapter $chapter = null): array;

    /**
     * 根据视频协议查找课时
     *
     * @return Lesson[]
     */
    public function findByVideoProtocol(string $protocol): array;

    /**
     * 检查课时是否有视频
     */
    public function hasVideo(Lesson $lesson): bool;

    /**
     * 获取课时播放时长（格式化为分钟）
     */
    public function getFormattedDuration(Lesson $lesson): string;

    /**
     * 检查课时是否属于指定课程
     */
    public function belongsToCourse(Lesson $lesson, Course $course): bool;

    /**
     * 获取课时在其章节中的位置
     */
    public function getLessonPositionInChapter(Lesson $lesson): int;

    /**
     * 获取课时的下一个课时
     */
    public function getNextLesson(Lesson $currentLesson): ?Lesson;

    /**
     * 获取课时的上一个课时
     */
    public function getPreviousLesson(Lesson $currentLesson): ?Lesson;
}
