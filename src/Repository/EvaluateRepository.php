<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * 课程评价仓储
 * 
 * @method Evaluate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evaluate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evaluate[]    findAll()
 * @method Evaluate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluate::class);
    }

    /**
     * 根据用户查找评价
     */
    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->andWhere('e.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'published')
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据课程查找评价
     */
    public function findByCourse(Course $course, ?string $status = 'published'): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.course = :course')
            ->setParameter('course', $course);

        if ($status) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('e.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 查找用户对特定课程的评价
     */
    public function findByUserAndCourse(string $userId, Course $course): ?Evaluate
    {
        return $this->createQueryBuilder('e')
            ->where('e.userId = :userId')
            ->andWhere('e.course = :course')
            ->setParameter('userId', $userId)
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 检查用户是否已评价课程
     */
    public function hasEvaluatedByUser(string $userId, Course $course): bool
    {
        return $this->findByUserAndCourse($userId, $course) !== null;
    }

    /**
     * 根据评分查找评价
     */
    public function findByRating(int $rating, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.rating = :rating')
            ->andWhere('e.status = :status')
            ->setParameter('rating', $rating)
            ->setParameter('status', 'published');

        if ($course) {
            $qb->andWhere('e.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('e.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 根据状态查找评价
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', $status)
            ->orderBy('e.createTime', 'DESC')
            ->getQuery()
            ->getResult();
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
            ->getSingleScalarResult();

        return round($result ?: 0, 2);
    }

    /**
     * 获取评价统计信息
     */
    public function getEvaluateStatistics(?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', 'published');

        if ($course) {
            $qb->andWhere('e.course = :course')
               ->setParameter('course', $course);
        }

        $totalEvaluates = $qb->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $averageRating = $qb->select('AVG(e.rating)')
            ->getQuery()
            ->getSingleScalarResult();

        // 各星级评价数量统计
        $ratingStats = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = $qb->select('COUNT(e.id)')
                ->andWhere('e.rating = :rating')
                ->setParameter('rating', $i)
                ->getQuery()
                ->getSingleScalarResult();
            $ratingStats[$i] = $count;
        }

        return [
            'total_evaluates' => $totalEvaluates,
            'average_rating' => round($averageRating ?: 0, 2),
            'rating_distribution' => $ratingStats,
        ];
    }

    /**
     * 搜索评价
     */
    public function searchEvaluates(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.content LIKE :keyword OR e.userNickname LIKE :keyword')
            ->andWhere('e.status = :status')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('status', 'published');

        if ($course) {
            $qb->andWhere('e.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('e.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 获取热门评价（按点赞数排序）
     */
    public function findPopularEvaluates(Course $course, int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.course = :course')
            ->andWhere('e.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('e.likeCount', 'DESC')
            ->addOrderBy('e.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取最新评价
     */
    public function findLatestEvaluates(Course $course, int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.course = :course')
            ->andWhere('e.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('e.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找待审核的评价
     */
    public function findPendingEvaluates(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('e.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 