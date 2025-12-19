<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;

/**
 * 课程播放控制仓储
 *
 * @extends ServiceEntityRepository<CoursePlayControl>
 */
#[AsRepository(entityClass: CoursePlayControl::class)]
final class CoursePlayControlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursePlayControl::class);
    }

    /**
     * 根据课程查找播放控制配置
     */
    public function findByCourse(Course $course): ?CoursePlayControl
    {
        $result = $this->createQueryBuilder('cpc')
            ->where('cpc.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof CoursePlayControl || null === $result);

        return $result;
    }

    /**
     * 查找启用播放控制的课程
     *
     * @return CoursePlayControl[]
     * @phpstan-return list<CoursePlayControl>
     */
    public function findEnabledControls(): array
    {
        /** @var list<CoursePlayControl> */

        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找禁用快进的课程
     *
     * @return CoursePlayControl[]
     * @phpstan-return list<CoursePlayControl>
     */
    public function findWithFastForwardDisabled(): array
    {
        /** @var list<CoursePlayControl> */

        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找启用水印的课程
     *
     * @return CoursePlayControl[]
     * @phpstan-return list<CoursePlayControl>
     */
    public function findWithWatermarkEnabled(): array
    {
        /** @var list<CoursePlayControl> */

        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.enableWatermark = :enableWatermark')
            ->setParameter('enabled', true)
            ->setParameter('enableWatermark', true)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找严格模式的课程（禁用快进和拖拽）
     *
     * @return CoursePlayControl[]
     * @phpstan-return list<CoursePlayControl>
     */
    public function findStrictModeControls(): array
    {
        /** @var list<CoursePlayControl> */

        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->andWhere('cpc.allowSeeking = :allowSeeking')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->setParameter('allowSeeking', false)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据设备数量限制查找
     *
     * @return CoursePlayControl[]
     * @phpstan-return list<CoursePlayControl>
     */
    public function findByMaxDeviceCount(int $maxDeviceCount): array
    {
        /** @var list<CoursePlayControl> */

        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.maxDeviceCount = :maxDeviceCount')
            ->setParameter('enabled', true)
            ->setParameter('maxDeviceCount', $maxDeviceCount)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取播放控制统计信息
     *
     * @return array{total_controls: int, enabled_controls: int, disabled_controls: int, fast_forward_disabled: int, watermark_enabled: int, strict_mode_count: int}
     */
    public function getPlayControlStatistics(): array
    {
        $totalControls = (int) $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $enabledControls = (int) $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $fastForwardDisabled = (int) $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $watermarkEnabled = (int) $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.enableWatermark = :enableWatermark')
            ->setParameter('enabled', true)
            ->setParameter('enableWatermark', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $strictModeCount = (int) $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->andWhere('cpc.allowSeeking = :allowSeeking')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->setParameter('allowSeeking', false)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total_controls' => $totalControls,
            'enabled_controls' => $enabledControls,
            'disabled_controls' => $totalControls - $enabledControls,
            'fast_forward_disabled' => $fastForwardDisabled,
            'watermark_enabled' => $watermarkEnabled,
            'strict_mode_count' => $strictModeCount,
        ];
    }

    /**
     * 查找需要更新播放凭证的课程
     *
     * @return CoursePlayControl[]
     * @phpstan-return list<CoursePlayControl>
     */
    public function findNeedingAuthUpdate(int $thresholdSeconds = 1800): array
    {
        /** @var list<CoursePlayControl> */

        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.playAuthDuration < :threshold')
            ->setParameter('enabled', true)
            ->setParameter('threshold', $thresholdSeconds)
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(CoursePlayControl $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoursePlayControl $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
