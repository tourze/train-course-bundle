<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseVersion;

/**
 * 课程版本仓储
 * 
 * @method CourseVersion|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseVersion|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseVersion[]    findAll()
 * @method CourseVersion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseVersion::class);
    }

    /**
     * 根据课程查找版本
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->setParameter('course', $course)
            ->orderBy('cv.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找课程的当前版本
     */
    public function findCurrentByCourse(Course $course): ?CourseVersion
    {
        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.isCurrent = :isCurrent')
            ->setParameter('course', $course)
            ->setParameter('isCurrent', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找已发布的版本
     */
    public function findPublishedByCourse(Course $course): array
    {
        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('cv.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据版本号查找
     */
    public function findByVersion(Course $course, string $version): ?CourseVersion
    {
        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.version = :version')
            ->setParameter('course', $course)
            ->setParameter('version', $version)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找最新版本
     */
    public function findLatestByCourse(Course $course): ?CourseVersion
    {
        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->orderBy('cv.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 根据状态查找版本
     */
    public function findByStatus(string $status, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('cv')
            ->where('cv.status = :status')
            ->setParameter('status', $status);

        if ((bool) $course) {
            $qb->andWhere('cv.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('cv.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 获取版本统计信息
     */
    public function getVersionStatistics(Course $course): array
    {
        $qb = $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->setParameter('course', $course);

        $totalVersions = $qb->select('COUNT(cv.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $publishedVersions = $qb->select('COUNT(cv.id)')
            ->andWhere('cv.status = :status')
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult();

        $draftVersions = $qb->select('COUNT(cv.id)')
            ->andWhere('cv.status = :status')
            ->setParameter('status', 'draft')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_versions' => $totalVersions,
            'published_versions' => $publishedVersions,
            'draft_versions' => $draftVersions,
            'archived_versions' => $totalVersions - $publishedVersions - $draftVersions,
        ];
    }

    /**
     * 搜索版本
     */
    public function searchVersions(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('cv')
            ->where('cv.version LIKE :keyword OR cv.title LIKE :keyword OR cv.description LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');

        if ((bool) $course) {
            $qb->andWhere('cv.course = :course')
               ->setParameter('course', $course);
        }

        return $qb->orderBy('cv.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 查找需要归档的版本（超过指定数量的旧版本）
     */
    public function findVersionsToArchive(Course $course, int $keepCount = 10): array
    {
        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.isCurrent = :isCurrent')
            ->andWhere('cv.status != :archivedStatus')
            ->setParameter('course', $course)
            ->setParameter('isCurrent', false)
            ->setParameter('archivedStatus', 'archived')
            ->orderBy('cv.createTime', 'DESC')
            ->setFirstResult($keepCount)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找旧版本（超过指定天数的版本）
     *
     * @param int $days 天数
     * @return CourseVersion[]
     */
    public function findOldVersions(int $days): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('cv')
            ->where('cv.createTime < :date')
            ->andWhere('cv.isCurrent = :isCurrent')
            ->setParameter('date', $date)
            ->setParameter('isCurrent', false)
            ->orderBy('cv.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 