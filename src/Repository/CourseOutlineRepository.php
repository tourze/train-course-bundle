<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;

/**
 * 课程大纲仓储
 *
 * @method CourseOutline|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseOutline|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseOutline[]    findAll()
 * @method CourseOutline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseOutlineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseOutline::class);
    }

    /**
     * 根据课程查找大纲
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('co')
            ->where('co.course = :course')
            ->setParameter('course', $course)
            ->orderBy('co.sortNumber', 'DESC')
            ->addOrderBy('co.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找已发布的大纲
     */
    public function findPublishedByCourse(Course $course): array
    {
        return $this->createQueryBuilder('co')
            ->where('co.course = :course')
            ->andWhere('co.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('co.sortNumber', 'DESC')
            ->addOrderBy('co.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 搜索大纲
     */
    public function searchOutlines(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('co')
            ->where('co.title LIKE :keyword OR co.learningObjectives LIKE :keyword OR co.contentPoints LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');

        if ((bool) $course) {
            $qb->andWhere('co.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('co.sortNumber', 'DESC')
                  ->addOrderBy('co.id', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 获取大纲统计信息
     */
    public function getOutlineStatistics(Course $course): array
    {
        $qb = $this->createQueryBuilder('co')
            ->where('co.course = :course')
            ->setParameter('course', $course);

        $totalOutlines = $qb->select('COUNT(co.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $publishedOutlines = $qb->select('COUNT(co.id)')
            ->andWhere('co.status = :status')
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult();

        $totalEstimatedMinutes = $qb->select('SUM(co.estimatedMinutes)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_outlines' => $totalOutlines,
            'published_outlines' => $publishedOutlines,
            'draft_outlines' => $totalOutlines - $publishedOutlines,
            'total_estimated_minutes' => $totalEstimatedMinutes ?? 0,
            'total_estimated_hours' => round(($totalEstimatedMinutes ?? 0) / 60, 2),
        ];
    }

    /**
     * 根据状态查找大纲
     */
    public function findByStatus(string $status, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('co')
            ->where('co.status = :status')
            ->setParameter('status', $status);

        if ((bool) $course) {
            $qb->andWhere('co.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('co.sortNumber', 'DESC')
                  ->addOrderBy('co.id', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
} 