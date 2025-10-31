<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课时仓储
 *
 * @extends ServiceEntityRepository<Lesson>
 */
#[AsRepository(entityClass: Lesson::class)]
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * 根据章节查找课时
     *
     * @return Lesson[]
     * @phpstan-return list<Lesson>
     */
    public function findByChapter(Chapter $chapter): array
    {
        /** @var list<Lesson> */
        return $this->createQueryBuilder('l')
            ->where('l.chapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->orderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据课程查找所有课时
     *
     * @return Lesson[]
     * @phpstan-return list<Lesson>
     */
    public function findByCourse(Course $course): array
    {
        /** @var list<Lesson> */
        return $this->createQueryBuilder('l')
            ->leftJoin('l.chapter', 'ch')
            ->addSelect('ch')
            ->leftJoin('ch.course', 'c')
            ->addSelect('c')
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
     * 查找有视频的课时
     *
     * @return Lesson[]
     * @phpstan-return list<Lesson>
     */
    public function findLessonsWithVideo(Chapter $chapter): array
    {
        /** @var list<Lesson> */
        return $this->createQueryBuilder('l')
            ->where('l.chapter = :chapter')
            ->andWhere('l.videoUrl IS NOT NULL')
            ->andWhere('l.videoUrl != :empty')
            ->setParameter('chapter', $chapter)
            ->setParameter('empty', '')
            ->orderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取课时统计信息
     *
     * @return array{total_lessons: int, lessons_with_video: int, lessons_without_video: int, total_duration_seconds: int, total_duration_hours: float}
     */
    public function getLessonStatistics(Chapter $chapter): array
    {
        $totalLessons = (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.chapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $lessonsWithVideo = (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.chapter = :chapter')
            ->andWhere('l.videoUrl IS NOT NULL')
            ->andWhere('l.videoUrl != :empty')
            ->setParameter('chapter', $chapter)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $totalDuration = (int) ($this->createQueryBuilder('l')
            ->select('SUM(l.durationSecond)')
            ->where('l.chapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);

        return [
            'total_lessons' => $totalLessons,
            'lessons_with_video' => $lessonsWithVideo,
            'lessons_without_video' => $totalLessons - $lessonsWithVideo,
            'total_duration_seconds' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 3600, 2),
        ];
    }

    /**
     * 搜索课时
     *
     * @return Lesson[]
     * @phpstan-return list<Lesson>
     */
    public function searchLessons(string $keyword, ?Chapter $chapter = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.title LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
        ;

        if ((bool) $chapter) {
            $qb->andWhere('l.chapter = :chapter')
                ->setParameter('chapter', $chapter)
            ;
        }

        /** @var list<Lesson> */
        return $qb->orderBy('l.sortNumber', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据视频协议查找课时
     *
     * @return Lesson[]
     * @phpstan-return list<Lesson>
     */
    public function findByVideoProtocol(string $protocol): array
    {
        /** @var list<Lesson> */
        return $this->createQueryBuilder('l')
            ->where('l.videoUrl LIKE :protocol')
            ->setParameter('protocol', $protocol . '%')
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Lesson $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Lesson $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
