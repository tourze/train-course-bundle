<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 章节仓储
 *
 * @method Chapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chapter[]    findAll()
 * @method Chapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapter::class);
    }

    /**
     * 根据课程查找章节
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('ch')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ch.sortNumber', 'DESC')
            ->addOrderBy('ch.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据课程查找章节（包含课时）
     */
    public function findByCourseWithLessons(Course $course): array
    {
        return $this->createQueryBuilder('ch')
            ->leftJoin('ch.lessons', 'l')
            ->addSelect('l')
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
     * 获取章节统计信息
     */
    public function getChapterStatistics(Course $course): array
    {
        $qb = $this->createQueryBuilder('ch')
            ->leftJoin('ch.lessons', 'l')
            ->where('ch.course = :course')
            ->setParameter('course', $course);

        $totalChapters = $qb->select('COUNT(DISTINCT ch.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalLessons = $qb->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalDuration = $qb->select('SUM(l.durationSecond)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_chapters' => $totalChapters,
            'total_lessons' => $totalLessons,
            'total_duration_seconds' => $totalDuration ?? 0,
            'total_duration_hours' => round(($totalDuration ?? 0) / 3600, 2),
        ];
    }

    /**
     * 搜索章节
     */
    public function searchChapters(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('ch')
            ->where('ch.title LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');

        if ((bool) $course) {
            $qb->andWhere('ch.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('ch.sortNumber', 'DESC')
                  ->addOrderBy('ch.id', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
