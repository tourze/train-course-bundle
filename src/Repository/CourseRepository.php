<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程仓储
 *
 * @extends ServiceEntityRepository<Course>
 */
#[AsRepository(entityClass: Course::class)]
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * 查找有效的课程
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function findValidCourses(): array
    {
        /** @var list<Course> */
        return $this->createQueryBuilder('c')
            ->where('c.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据分类查找课程
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function findByCategory(Catalog $category): array
    {
        /** @var list<Course> */
        return $this->createQueryBuilder('c')
            ->where('c.category = :category')
            ->andWhere('c.valid = :valid')
            ->setParameter('category', $category)
            ->setParameter('valid', true)
            ->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据分类列表查找课程
     *
     * @param Catalog[] $categories
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function findByCategories(array $categories): array
    {
        /** @var list<Course> */
        return $this->createQueryBuilder('c')
            ->where('c.category IN (:categories)')
            ->andWhere('c.valid = :valid')
            ->setParameter('categories', $categories)
            ->setParameter('valid', true)
            ->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索课程
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function searchCourses(string $keyword, ?Catalog $category = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.valid = :valid')
            ->andWhere('c.title LIKE :keyword OR c.description LIKE :keyword OR c.teacherName LIKE :keyword')
            ->setParameter('valid', true)
            ->setParameter('keyword', '%' . $keyword . '%')
        ;

        if ((bool) $category) {
            $qb->andWhere('c.category = :category')
                ->setParameter('category', $category)
            ;
        }

        /** @var list<Course> */
        return $qb->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取课程统计信息
     */
    /** @return array{total_courses: int, valid_courses: int, invalid_courses: int} */
    public function getStatistics(): array
    {
        $totalCourses = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $validCourses = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.valid = :valid')
            ->setParameter('valid', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total_courses' => $totalCourses,
            'valid_courses' => $validCourses,
            'invalid_courses' => $totalCourses - $validCourses,
        ];
    }

    /**
     * 创建基础查询构建器（包含关联数据预加载）
     */
    public function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.category', 'cat')
            ->addSelect('cat')
            ->leftJoin('c.chapters', 'ch')
            ->addSelect('ch')
            ->leftJoin('ch.lessons', 'l')
            ->addSelect('l')
        ;
    }

    /**
     * 根据价格范围查找课程
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function findByPriceRange(?float $minPrice = null, ?float $maxPrice = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.valid = :valid')
            ->setParameter('valid', true)
        ;

        if (null !== $minPrice) {
            $qb->andWhere('c.price >= :minPrice')
                ->setParameter('minPrice', $minPrice)
            ;
        }

        if (null !== $maxPrice) {
            $qb->andWhere('c.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice)
            ;
        }

        /** @var list<Course> */
        return $qb->orderBy('c.price', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找自指定日期以来更新的课程
     *
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function findUpdatedSince(\DateTimeInterface $since): array
    {
        /** @var list<Course> */
        return $this->createQueryBuilder('c')
            ->where('c.createTime >= :since')
            ->setParameter('since', $since)
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找过期的课程
     *
     * @param \DateTimeInterface|null $date 指定日期，默认为当前时间
     *
     * @return Course[]
     * @phpstan-return list<Course>
     */
    public function findExpiredCourses(?\DateTimeInterface $date = null): array
    {
        if (null === $date) {
            $date = new \DateTime();
        }

        /** @var list<Course> */
        return $this->createQueryBuilder('c')
            ->where('c.valid = false')
            ->orWhere('c.createTime < :date')
            ->setParameter('date', $date)
            ->orderBy('c.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Course $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Course $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
