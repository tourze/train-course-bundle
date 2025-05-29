<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课时仓储
 * 
 * @method Lesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lesson[]    findAll()
 * @method Lesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * 根据章节查找课时
     */
    public function findByChapter(Chapter $chapter): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.chapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->orderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据课程查找所有课时
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.chapter', 'ch')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ch.sortNumber', 'DESC')
            ->addOrderBy('ch.id', 'ASC')
            ->addOrderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找有视频的课时
     */
    public function findLessonsWithVideo(Chapter $chapter): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.chapter = :chapter')
            ->andWhere('l.videoUrl IS NOT NULL')
            ->andWhere('l.videoUrl != :empty')
            ->setParameter('chapter', $chapter)
            ->setParameter('empty', '')
            ->orderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取课时统计信息
     */
    public function getLessonStatistics(Chapter $chapter): array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.chapter = :chapter')
            ->setParameter('chapter', $chapter);

        $totalLessons = $qb->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $lessonsWithVideo = $qb->select('COUNT(l.id)')
            ->andWhere('l.videoUrl IS NOT NULL')
            ->andWhere('l.videoUrl != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        $totalDuration = $qb->select('SUM(l.durationSecond)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_lessons' => $totalLessons,
            'lessons_with_video' => $lessonsWithVideo,
            'lessons_without_video' => $totalLessons - $lessonsWithVideo,
            'total_duration_seconds' => $totalDuration ?: 0,
            'total_duration_hours' => round(($totalDuration ?: 0) / 3600, 2),
        ];
    }

    /**
     * 搜索课时
     */
    public function searchLessons(string $keyword, ?Chapter $chapter = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.title LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');

        if ($chapter) {
            $qb->andWhere('l.chapter = :chapter')
               ->setParameter('chapter', $chapter);
        }

        return $qb->orderBy('l.sortNumber', 'DESC')
                  ->addOrderBy('l.id', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 根据视频协议查找课时
     */
    public function findByVideoProtocol(string $protocol): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.videoUrl LIKE :protocol')
            ->setParameter('protocol', $protocol . '%')
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
