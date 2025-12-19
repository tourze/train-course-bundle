<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;

/**
 * 课程审核仓储
 *
 * @extends ServiceEntityRepository<CourseAudit>
 */
#[AsRepository(entityClass: CourseAudit::class)]
final class CourseAuditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseAudit::class);
    }

    /**
     * 根据课程查找审核记录
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findByCourse(Course $course): array
    {
        /** @var list<CourseAudit> */

        return $this->createQueryBuilder('ca')
            ->where('ca.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ca.auditLevel', 'ASC')
            ->addOrderBy('ca.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据状态查找审核记录
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findByStatus(string $status): array
    {
        /** @var list<CourseAudit> */

        return $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->setParameter('status', $status)
            ->orderBy('ca.priority', 'DESC')
            ->addOrderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找待审核的记录
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findPendingAudits(?string $auditor = null): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->setParameter('status', 'pending')
        ;

        if ((bool) $auditor) {
            $qb->andWhere('ca.auditor = :auditor OR ca.auditor IS NULL')
                ->setParameter('auditor', $auditor)
            ;
        }

        /** @var list<CourseAudit> */

        return $qb->orderBy('ca.priority', 'DESC')
            ->addOrderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找超时的审核记录
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findOverdueAudits(): array
    {
        /** @var list<CourseAudit> */

        return $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->andWhere('ca.deadline < :now')
            ->setParameter('status', 'pending')
            ->setParameter('now', new \DateTime())
            ->orderBy('ca.deadline', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据审核类型查找记录
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findByAuditType(string $auditType, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->where('ca.auditType = :auditType')
            ->setParameter('auditType', $auditType)
        ;

        if ((bool) $status) {
            $qb->andWhere('ca.status = :status')
                ->setParameter('status', $status)
            ;
        }

        /** @var list<CourseAudit> */

        return $qb->orderBy('ca.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取审核统计信息
     *
     * @return array{total_audits: int, pending_audits: int, approved_audits: int, rejected_audits: int, approval_rate: float}
     */
    public function getAuditStatistics(?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('ca');

        if ((bool) $course) {
            $qb->where('ca.course = :course')
                ->setParameter('course', $course)
            ;
        }

        $totalAudits = (int) $qb->select('COUNT(ca.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $pendingAudits = (int) $qb->select('COUNT(ca.id)')
            ->andWhere('ca.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $approvedAudits = (int) $qb->select('COUNT(ca.id)')
            ->andWhere('ca.status = :status')
            ->setParameter('status', 'approved')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $rejectedAudits = (int) $qb->select('COUNT(ca.id)')
            ->andWhere('ca.status = :status')
            ->setParameter('status', 'rejected')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return [
            'total_audits' => $totalAudits,
            'pending_audits' => $pendingAudits,
            'approved_audits' => $approvedAudits,
            'rejected_audits' => $rejectedAudits,
            'approval_rate' => $totalAudits > 0 ? round($approvedAudits / $totalAudits * 100, 2) : 0,
        ];
    }

    /**
     * 查找指定审核人员的审核记录
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findByAuditor(string $auditor, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->where('ca.auditor = :auditor')
            ->setParameter('auditor', $auditor)
        ;

        if ((bool) $status) {
            $qb->andWhere('ca.status = :status')
                ->setParameter('status', $status)
            ;
        }

        /** @var list<CourseAudit> */

        return $qb->orderBy('ca.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找课程的最新审核记录
     */
    public function findLatestByCourse(Course $course): ?CourseAudit
    {
        $result = $this->createQueryBuilder('ca')
            ->where('ca.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ca.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof CourseAudit || null === $result);

        return $result;
    }

    /**
     * 查找超时的审核记录
     *
     * @param int $timeoutHours 超时小时数
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findTimeoutAudits(int $timeoutHours): array
    {
        $timeoutDate = new \DateTime();
        $timeoutDate->modify("-{$timeoutHours} hours");

        /** @var list<CourseAudit> */

        return $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->andWhere('ca.createTime < :timeoutDate')
            ->setParameter('status', 'pending')
            ->setParameter('timeoutDate', $timeoutDate)
            ->orderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找旧的审核记录（超过指定天数）
     *
     * @param int $days 天数
     *
     * @return CourseAudit[]
     * @phpstan-return list<CourseAudit>
     */
    public function findOldAudits(int $days): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        /** @var list<CourseAudit> */

        return $this->createQueryBuilder('ca')
            ->where('ca.createTime < :date')
            ->andWhere('ca.status != :pendingStatus')
            ->setParameter('date', $date)
            ->setParameter('pendingStatus', 'pending')
            ->orderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(CourseAudit $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CourseAudit $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
