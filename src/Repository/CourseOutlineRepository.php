<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;

/**
 * 课程大纲仓储
 *
 * @extends ServiceEntityRepository<CourseOutline>
 */
#[AsRepository(entityClass: CourseOutline::class)]
class CourseOutlineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseOutline::class);
    }

    /**
     * 根据课程查找大纲
     *
     * @return CourseOutline[]
     * @phpstan-return list<CourseOutline>
     */
    public function findByCourse(Course $course): array
    {
        /** @var list<CourseOutline> */

        return $this->createQueryBuilder('co')
            ->where('co.course = :course')
            ->setParameter('course', $course)
            ->orderBy('co.sortNumber', 'DESC')
            ->addOrderBy('co.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找已发布的大纲
     *
     * @return CourseOutline[]
     * @phpstan-return list<CourseOutline>
     */
    public function findPublishedByCourse(Course $course): array
    {
        /** @var list<CourseOutline> */

        return $this->createQueryBuilder('co')
            ->where('co.course = :course')
            ->andWhere('co.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('co.sortNumber', 'DESC')
            ->addOrderBy('co.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索大纲
     *
     * @return CourseOutline[]
     * @phpstan-return list<CourseOutline>
     */
    public function searchOutlines(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('co')
            ->where('co.title LIKE :keyword OR co.learningObjectives LIKE :keyword OR co.contentPoints LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
        ;

        if ((bool) $course) {
            $qb->andWhere('co.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<CourseOutline> */

        return $qb->orderBy('co.sortNumber', 'DESC')
            ->addOrderBy('co.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取大纲统计信息
     *
     * @return array{total_outlines: int, published_outlines: int, draft_outlines: int, total_estimated_minutes: int, total_estimated_hours: float}
     */
    public function getOutlineStatistics(Course $course): array
    {
        $totalOutlines = (int) $this->createQueryBuilder('co')
            ->select('COUNT(co.id)')
            ->where('co.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $publishedOutlines = (int) $this->createQueryBuilder('co')
            ->select('COUNT(co.id)')
            ->where('co.course = :course')
            ->andWhere('co.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $totalEstimatedMinutes = (int) ($this->createQueryBuilder('co')
            ->select('SUM(co.estimatedMinutes)')
            ->where('co.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);

        return [
            'total_outlines' => $totalOutlines,
            'published_outlines' => $publishedOutlines,
            'draft_outlines' => $totalOutlines - $publishedOutlines,
            'total_estimated_minutes' => $totalEstimatedMinutes,
            'total_estimated_hours' => round($totalEstimatedMinutes / 60, 2),
        ];
    }

    /**
     * 根据状态查找大纲
     *
     * @return CourseOutline[]
     * @phpstan-return list<CourseOutline>
     */
    public function findByStatus(string $status, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('co')
            ->where('co.status = :status')
            ->setParameter('status', $status)
        ;

        if ((bool) $course) {
            $qb->andWhere('co.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<CourseOutline> */

        return $qb->orderBy('co.sortNumber', 'DESC')
            ->addOrderBy('co.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(CourseOutline $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseOutline $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
