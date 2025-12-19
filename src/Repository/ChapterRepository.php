<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 章节仓储
 *
 * @extends ServiceEntityRepository<Chapter>
 */
#[AsRepository(entityClass: Chapter::class)]
final class ChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapter::class);
    }

    /**
     * 根据课程查找章节
     * @return array<Chapter>
     * @phpstan-return list<Chapter>
     */
    public function findByCourse(Course $course): array
    {
        /** @var list<Chapter> */
        return $this->createQueryBuilder('ch')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ch.sortNumber', 'DESC')
            ->addOrderBy('ch.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据课程查找章节（包含课时）
     * @return array<Chapter>
     * @phpstan-return list<Chapter>
     */
    public function findByCourseWithLessons(Course $course): array
    {
        /** @var list<Chapter> */
        return $this->createQueryBuilder('ch')
            ->leftJoin('ch.course', 'c')
            ->addSelect('c')
            ->leftJoin('ch.lessons', 'l')
            ->addSelect('l')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ch.sortNumber', 'DESC')
            ->addOrderBy('ch.id', 'ASC')
            ->addOrderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取章节统计信息
     */
    /** @return array{total_chapters: int, total_lessons: int, total_duration_seconds: int, total_duration_hours: float} */
    public function getChapterStatistics(Course $course): array
    {
        $totalChapters = (int) $this->createQueryBuilder('ch')
            ->select('COUNT(DISTINCT ch.id)')
            ->leftJoin('ch.lessons', 'l')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $totalLessons = (int) $this->createQueryBuilder('ch')
            ->select('COUNT(l.id)')
            ->leftJoin('ch.lessons', 'l')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $totalDuration = (int) ($this->createQueryBuilder('ch')
            ->select('SUM(l.durationSecond)')
            ->leftJoin('ch.lessons', 'l')
            ->where('ch.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);

        return [
            'total_chapters' => $totalChapters,
            'total_lessons' => $totalLessons,
            'total_duration_seconds' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 3600, 2),
        ];
    }

    /**
     * 搜索章节
     * @return array<Chapter>
     * @phpstan-return list<Chapter>
     */
    public function searchChapters(string $keyword, ?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('ch')
            ->where('ch.title LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
        ;

        if (null !== $course) {
            $qb->andWhere('ch.course = :course')
                ->setParameter('course', $course)
            ;
        }

        /** @var list<Chapter> */
        return $qb->orderBy('ch.sortNumber', 'DESC')
            ->addOrderBy('ch.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Chapter $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Chapter $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
