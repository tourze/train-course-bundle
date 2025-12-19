<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseVersion;

/**
 * 课程版本仓储
 *
 * @extends ServiceEntityRepository<CourseVersion>
 */
#[AsRepository(entityClass: CourseVersion::class)]
final class CourseVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseVersion::class);
    }

    /**
     * 根据课程查找版本
     *
     * @return CourseVersion[]
     * @phpstan-return list<CourseVersion>
     */
    public function findByCourse(Course $course): array
    {
        /** @var list<CourseVersion> */

        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->setParameter('course', $course)
            ->orderBy('cv.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找课程的当前版本
     */
    public function findCurrentByCourse(Course $course): ?CourseVersion
    {
        $result = $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.isCurrent = :isCurrent')
            ->setParameter('course', $course)
            ->setParameter('isCurrent', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof CourseVersion || null === $result);

        return $result;
    }

    /**
     * 查找已发布的版本
     *
     * @return CourseVersion[]
     * @phpstan-return list<CourseVersion>
     */
    public function findPublishedByCourse(Course $course): array
    {
        /** @var list<CourseVersion> */

        return $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.status = :status')
            ->setParameter('course', $course)
            ->setParameter('status', 'published')
            ->orderBy('cv.publishedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据版本号查找
     */
    public function findByVersion(Course $course, string $version): ?CourseVersion
    {
        $result = $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->andWhere('cv.version = :version')
            ->setParameter('course', $course)
            ->setParameter('version', $version)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof CourseVersion || null === $result);

        return $result;
    }

    /**
     * 查找最新版本
     */
    public function findLatestByCourse(Course $course): ?CourseVersion
    {
        $result = $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->setParameter('course', $course)
            ->orderBy('cv.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof CourseVersion || null === $result);

        return $result;
    }

    /**
     * 根据状态查找版本
     *
     * @return CourseVersion[]
     * @phpstan-return list<CourseVersion>
     */
    public function findByStatus(string $status, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('cv')
            ->where('cv.status = :status')
            ->setParameter('status', $status)
        ;

        if ((bool) $course) {
            $qb->andWhere('cv.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<CourseVersion> */

        return $qb->orderBy('cv.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取版本统计信息
     *
     * @return array{total_versions: int, published_versions: int, draft_versions: int, archived_versions: int}
     */
    public function getVersionStatistics(Course $course): array
    {
        $qb = $this->createQueryBuilder('cv')
            ->where('cv.course = :course')
            ->setParameter('course', $course)
        ;

        $totalVersions = (int) $qb->select('COUNT(cv.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $publishedVersions = (int) $qb->select('COUNT(cv.id)')
            ->andWhere('cv.status = :status')
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $draftVersions = (int) $qb->select('COUNT(cv.id)')
            ->andWhere('cv.status = :status')
            ->setParameter('status', 'draft')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total_versions' => $totalVersions,
            'published_versions' => $publishedVersions,
            'draft_versions' => $draftVersions,
            'archived_versions' => $totalVersions - $publishedVersions - $draftVersions,
        ];
    }

    /**
     * 搜索版本
     *
     * @return CourseVersion[]
     * @phpstan-return list<CourseVersion>
     */
    public function searchVersions(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('cv')
            ->where('cv.version LIKE :keyword OR cv.title LIKE :keyword OR cv.description LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
        ;

        if ((bool) $course) {
            $qb->andWhere('cv.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<CourseVersion> */

        return $qb->orderBy('cv.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找需要归档的版本（超过指定数量的旧版本）
     *
     * @return CourseVersion[]
     * @phpstan-return list<CourseVersion>
     */
    public function findVersionsToArchive(Course $course, int $keepCount = 10): array
    {
        /** @var list<CourseVersion> */

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
            ->getResult()
        ;
    }

    /**
     * 查找旧版本（超过指定天数的版本）
     *
     * @param int $days 天数
     *
     * @return CourseVersion[]
     * @phpstan-return list<CourseVersion>
     */
    public function findOldVersions(int $days): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        /** @var list<CourseVersion> */

        return $this->createQueryBuilder('cv')
            ->where('cv.createTime < :date')
            ->andWhere('cv.isCurrent = :isCurrent')
            ->setParameter('date', $date)
            ->setParameter('isCurrent', false)
            ->orderBy('cv.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(CourseVersion $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseVersion $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
