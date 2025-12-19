<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * 课程评价仓储
 *
 * @extends ServiceEntityRepository<Evaluate>
 */
#[AsRepository(entityClass: Evaluate::class)]
final class EvaluateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluate::class);
    }

    /**
     * 根据用户查找评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findByUser(string $userId): array
    {
        /** @var list<Evaluate> */
        return $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->andWhere('e.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'published')
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据课程查找评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findByCourse(Course $course, ?string $status = 'published'): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.course = :course')
            ->setParameter('course', $course)
        ;

        if ((bool) $status) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', $status)
            ;
        }

        /** @var list<Evaluate> */
        return $qb->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找用户对特定课程的评价
     */
    public function findByUserAndCourse(string $userId, Course $course): ?Evaluate
    {
        $result = $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->andWhere('e.course = :course')
            ->setParameter('userId', $userId)
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof Evaluate || null === $result);

        return $result;
    }

    /**
     * 检查用户是否已评价课程
     */
    public function hasEvaluatedByUser(string $userId, Course $course): bool
    {
        return null !== $this->findByUserAndCourse($userId, $course);
    }

    /**
     * 根据评分查找评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findByRating(int $rating, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.rating = :rating')
            ->andWhere('e.status = :status')
            ->setParameter('rating', $rating)
            ->setParameter('status', 'published')
        ;

        if ((bool) $course) {
            $qb->andWhere('e.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<Evaluate> */
        return $qb->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据状态查找评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findByStatus(string $status): array
    {
        /** @var list<Evaluate> */
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', $status)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取课程的平均评分
     */
    public function getAverageRating(Course $course): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.rating) as avg_rating')
            ->where('e.course = :course')
            ->andWhere('e.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return round((float) ($result ?? 0), 2);
    }

    /**
     * 获取评价统计信息
     *
     * @return array{total_evaluates: int, average_rating: float, rating_distribution: array<int, int>}
     */
    public function getEvaluateStatistics(?Course $course = null): array
    {
        $qbTotal = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.status = :status')
            ->setParameter('status', 'published')
        ;

        if ((bool) $course) {
            $qbTotal->andWhere('e.course = :course')
                ->setParameter('course', $course)
            ;
        }

        $totalEvaluates = (int) $qbTotal
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $qbAvg = $this->createQueryBuilder('e')
            ->select('AVG(e.rating)')
            ->where('e.status = :status')
            ->setParameter('status', 'published')
        ;

        if ((bool) $course) {
            $qbAvg->andWhere('e.course = :course')
                ->setParameter('course', $course)
            ;
        }

        $averageRating = (float) ($qbAvg
            ->getQuery()
            ->getSingleScalarResult() ?? 0);

        // 各星级评价数量统计
        $ratingStats = [];
        for ($i = 1; $i <= 5; ++$i) {
            $qb = $this->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.status = :status')
                ->andWhere('e.rating = :rating')
                ->setParameter('status', 'published')
                ->setParameter('rating', $i)
            ;

            if ((bool) $course) {
                $qb->andWhere('e.course = :course')
                    ->setParameter('course', $course)
                ;
            }

            $count = (int) $qb->getQuery()
                ->getSingleScalarResult()
            ;
            $ratingStats[$i] = $count;
        }

        return [
            'total_evaluates' => $totalEvaluates,
            'average_rating' => round($averageRating, 2),
            'rating_distribution' => $ratingStats,
        ];
    }

    /**
     * 搜索评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function searchEvaluates(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.content LIKE :keyword OR e.userNickname LIKE :keyword')
            ->andWhere('e.status = :status')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('status', 'published')
        ;

        if ((bool) $course) {
            $qb->andWhere('e.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<Evaluate> */
        return $qb->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取热门评价（按点赞数排序）
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findPopularEvaluates(Course $course, int $limit = 10): array
    {
        /** @var list<Evaluate> */
        return $this->createQueryBuilder('e')
            ->where('e.course = :course')
            ->andWhere('e.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('e.likeCount', 'DESC')
            ->addOrderBy('e.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取最新评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findLatestEvaluates(Course $course, int $limit = 10): array
    {
        /** @var list<Evaluate> */
        return $this->createQueryBuilder('e')
            ->where('e.course = :course')
            ->andWhere('e.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('e.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找待审核的评价
     *
     * @return array<Evaluate>
     * @phpstan-return list<Evaluate>
     */
    public function findPendingEvaluates(): array
    {
        /** @var list<Evaluate> */
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('e.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Evaluate $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Evaluate $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
