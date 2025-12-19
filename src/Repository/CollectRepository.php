<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程收藏仓储
 *
 * @extends ServiceEntityRepository<Collect>
 */
#[AsRepository(entityClass: Collect::class)]
final class CollectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collect::class);
    }

    /**
     * 根据用户查找收藏
     * @return array<Collect>
     * @phpstan-return list<Collect>
     */
    public function findByUser(string $userId): array
    {
        /** @var list<Collect> */
        return $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->andWhere('c.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'active')
            ->orderBy('c.isTop', 'DESC')
            ->addOrderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据课程查找收藏
     * @return array<Collect>
     * @phpstan-return list<Collect>
     */
    public function findByCourse(Course $course): array
    {
        /** @var list<Collect> */
        return $this->createQueryBuilder('c')
            ->where('c.course = :course')
            ->andWhere('c.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'active')
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找用户对特定课程的收藏
     */
    public function findByUserAndCourse(string $userId, Course $course): ?Collect
    {
        $result = $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->andWhere('c.course = :course')
            ->setParameter('userId', $userId)
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof Collect || null === $result);

        return $result;
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
     * @return array<Collect>
     * @phpstan-return list<Collect>
     */
    public function findByGroup(string $userId, string $group): array
    {
        /** @var list<Collect> */
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
            ->getResult()
        ;
    }

    /**
     * 获取用户的收藏分组列表
     *
     * @return array<int, array{group: string|null, count: int}>
     */
    public function getUserCollectGroups(string $userId): array
    {
        /** @var list<array{collectGroup: string|null, count: int}> */
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
            ->getResult()
        ;

        return array_map(
            static fn (array $item): array => [
                'group' => $item['collectGroup'],
                'count' => $item['count'],
            ],
            $result
        );
    }

    /**
     * 获取收藏统计信息
     *
     * @return array{total_collects: int, top_collects: int, normal_collects: int}
     */
    public function getCollectStatistics(?string $userId = null, ?Course $course = null): array
    {
        $qbTotal = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', 'active')
        ;

        if ((bool) $userId) {
            $qbTotal->andWhere('c.userId = :userId')
                ->setParameter('userId', $userId)
            ;
        }

        if ((bool) $course) {
            $qbTotal->andWhere('c.course = :course')
                ->setParameter('course', $course)
            ;
        }

        $totalCollects = (int) $qbTotal
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $qbTop = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->andWhere('c.isTop = :isTop')
            ->setParameter('status', 'active')
            ->setParameter('isTop', true)
        ;

        if ((bool) $userId) {
            $qbTop->andWhere('c.userId = :userId')
                ->setParameter('userId', $userId)
            ;
        }

        if ((bool) $course) {
            $qbTop->andWhere('c.course = :course')
                ->setParameter('course', $course)
            ;
        }

        $topCollects = (int) $qbTop
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total_collects' => $totalCollects,
            'top_collects' => $topCollects,
            'normal_collects' => $totalCollects - $topCollects,
        ];
    }

    /**
     * 搜索收藏
     * @return array<Collect>
     * @phpstan-return list<Collect>
     */
    public function searchCollects(string $userId, string $keyword): array
    {
        /** @var list<Collect> */
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
            ->getResult()
        ;
    }

    public function save(Collect $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Collect $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
