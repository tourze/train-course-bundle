<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程收藏仓储
 * 
 * @method Collect|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collect|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collect[]    findAll()
 * @method Collect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collect::class);
    }

    /**
     * 根据用户查找收藏
     */
    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->andWhere('c.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'active')
            ->orderBy('c.isTop', 'DESC')
            ->addOrderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据课程查找收藏
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.course = :course')
            ->andWhere('c.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'active')
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找用户对特定课程的收藏
     */
    public function findByUserAndCourse(string $userId, Course $course): ?Collect
    {
        return $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->andWhere('c.course = :course')
            ->setParameter('userId', $userId)
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 检查用户是否收藏了课程
     */
    public function isCollectedByUser(string $userId, Course $course): bool
    {
        $collect = $this->findByUserAndCourse($userId, $course);
        return null !== $collect && $collect->isActive();
    }

    /**
     * 根据收藏分组查找
     */
    public function findByGroup(string $userId, string $group): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->andWhere('c.collectGroup = :group')
            ->andWhere('c.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('group', $group)
            ->setParameter('status', 'active')
            ->orderBy('c.isTop', 'DESC')
            ->addOrderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取用户的收藏分组列表
     */
    public function getUserCollectGroups(string $userId): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.collectGroup, COUNT(c.id) as count')
            ->where('c.userId = :userId')
            ->andWhere('c.status = :status')
            ->andWhere('c.collectGroup IS NOT NULL')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'active')
            ->groupBy('c.collectGroup')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(function ($item) {
            return [
                'group' => $item['collectGroup'],
                'count' => $item['count'],
            ];
        }, $result);
    }

    /**
     * 获取收藏统计信息
     */
    public function getCollectStatistics(?string $userId = null, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', 'active');

        if ((bool) $userId) {
            $qb->andWhere('c.userId = :userId')
               ->setParameter('userId', $userId);
        }

        if ((bool) $course) {
            $qb->andWhere('c.course = :course')
               ->setParameter('course', $course);
        }

        $totalCollects = $qb->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $topCollects = $qb->select('COUNT(c.id)')
            ->andWhere('c.isTop = :isTop')
            ->setParameter('isTop', true)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_collects' => $totalCollects,
            'top_collects' => $topCollects,
            'normal_collects' => $totalCollects - $topCollects,
        ];
    }

    /**
     * 搜索收藏
     */
    public function searchCollects(string $userId, string $keyword): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.course', 'course')
            ->where('c.userId = :userId')
            ->andWhere('c.status = :status')
            ->andWhere('course.title LIKE :keyword OR c.note LIKE :keyword')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'active')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('c.isTop', 'DESC')
            ->addOrderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 