<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程仓储
 * 
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 * @method Course[]    findAll()
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * 查找有效的课程
     */
    public function findValidCourses(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据分类查找课程
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.category = :category')
            ->andWhere('c.valid = :valid')
            ->setParameter('category', $category)
            ->setParameter('valid', true)
            ->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据分类列表查找课程
     */
    public function findByCategories(array $categories): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.category IN (:categories)')
            ->andWhere('c.valid = :valid')
            ->setParameter('categories', $categories)
            ->setParameter('valid', true)
            ->orderBy('c.sortNumber', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 搜索课程
     */
    public function searchCourses(string $keyword, ?Category $category = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.valid = :valid')
            ->andWhere('c.title LIKE :keyword OR c.description LIKE :keyword OR c.teacherName LIKE :keyword')
            ->setParameter('valid', true)
            ->setParameter('keyword', '%' . $keyword . '%');

        if ((bool) $category) {
            $qb->andWhere('c.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->orderBy('c.sortNumber', 'DESC')
                  ->addOrderBy('c.id', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 获取课程统计信息
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('c');
        
        $totalCourses = $qb->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $validCourses = $qb->select('COUNT(c.id)')
            ->where('c.valid = :valid')
            ->setParameter('valid', true)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_courses' => $totalCourses,
            'valid_courses' => $validCourses,
            'invalid_courses' => $totalCourses - $validCourses,
        ];
    }

    /**
     * 创建基础查询构建器
     */
    public function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.category', 'cat')
            ->leftJoin('c.chapters', 'ch')
            ->leftJoin('ch.lessons', 'l');
    }

    /**
     * 根据价格范围查找课程
     */
    public function findByPriceRange(?float $minPrice = null, ?float $maxPrice = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.valid = :valid')
            ->setParameter('valid', true);

        if ($minPrice !== null) {
            $qb->andWhere('c.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('c.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->orderBy('c.price', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 查找自指定日期以来更新的课程
     * 
     * @param \DateTimeInterface $since
     * @return Course[]
     */
    public function findUpdatedSince(\DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.updateTime >= :since')
            ->setParameter('since', $since)
            ->orderBy('c.updateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找过期的课程
     * 
     * @param \DateTimeInterface|null $date 指定日期，默认为当前时间
     * @return Course[]
     */
    public function findExpiredCourses(?\DateTimeInterface $date = null): array
    {
        if ($date === null) {
            $date = new \DateTime();
        }

        return $this->createQueryBuilder('c')
            ->where('c.validEndTime IS NOT NULL')
            ->andWhere('c.validEndTime < :date')
            ->setParameter('date', $date)
            ->orderBy('c.validEndTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
