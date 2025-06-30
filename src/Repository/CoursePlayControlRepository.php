<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;

/**
 * 课程播放控制仓储
 *
 * @method CoursePlayControl|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursePlayControl|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursePlayControl[]    findAll()
 * @method CoursePlayControl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursePlayControlRepository extends ServiceEntityRepository
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
        return $this->createQueryBuilder('cpc')
            ->where('cpc.course = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找启用播放控制的课程
     */
    public function findEnabledControls(): array
    {
        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找禁用快进的课程
     */
    public function findWithFastForwardDisabled(): array
    {
        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找启用水印的课程
     */
    public function findWithWatermarkEnabled(): array
    {
        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.enableWatermark = :enableWatermark')
            ->setParameter('enabled', true)
            ->setParameter('enableWatermark', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找严格模式的课程（禁用快进和拖拽）
     */
    public function findStrictModeControls(): array
    {
        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->andWhere('cpc.allowSeeking = :allowSeeking')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->setParameter('allowSeeking', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据设备数量限制查找
     */
    public function findByMaxDeviceCount(int $maxDeviceCount): array
    {
        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.maxDeviceCount = :maxDeviceCount')
            ->setParameter('enabled', true)
            ->setParameter('maxDeviceCount', $maxDeviceCount)
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取播放控制统计信息
     */
    public function getPlayControlStatistics(): array
    {
        $totalControls = $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $enabledControls = $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getSingleScalarResult();

        $fastForwardDisabled = $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->getQuery()
            ->getSingleScalarResult();

        $watermarkEnabled = $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.enableWatermark = :enableWatermark')
            ->setParameter('enabled', true)
            ->setParameter('enableWatermark', true)
            ->getQuery()
            ->getSingleScalarResult();

        $strictModeCount = $this->createQueryBuilder('cpc')
            ->select('COUNT(cpc.id)')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.allowFastForward = :allowFastForward')
            ->andWhere('cpc.allowSeeking = :allowSeeking')
            ->setParameter('enabled', true)
            ->setParameter('allowFastForward', false)
            ->setParameter('allowSeeking', false)
            ->getQuery()
            ->getSingleScalarResult();

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
     */
    public function findNeedingAuthUpdate(int $thresholdSeconds = 1800): array
    {
        return $this->createQueryBuilder('cpc')
            ->where('cpc.enabled = :enabled')
            ->andWhere('cpc.playAuthDuration < :threshold')
            ->setParameter('enabled', true)
            ->setParameter('threshold', $thresholdSeconds)
            ->getQuery()
            ->getResult();
    }
} 