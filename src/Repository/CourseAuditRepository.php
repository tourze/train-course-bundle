<?php

namespace Tourze\TrainCourseBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;

/**
 * 课程审核仓储
 * 
 * @method CourseAudit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseAudit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseAudit[]    findAll()
 * @method CourseAudit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseAuditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseAudit::class);
    }

    /**
     * 根据课程查找审核记录
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ca.auditLevel', 'ASC')
            ->addOrderBy('ca.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据状态查找审核记录
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->setParameter('status', $status)
            ->orderBy('ca.priority', 'DESC')
            ->addOrderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找待审核的记录
     */
    public function findPendingAudits(?string $auditor = null): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->setParameter('status', 'pending');

        if ((bool) $auditor) {
            $qb->andWhere('ca.auditor = :auditor OR ca.auditor IS NULL')
               ->setParameter('auditor', $auditor);
        }

        return $qb->orderBy('ca.priority', 'DESC')
                  ->addOrderBy('ca.createTime', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 查找超时的审核记录
     */
    public function findOverdueAudits(): array
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->andWhere('ca.deadline < :now')
            ->setParameter('status', 'pending')
            ->setParameter('now', new \DateTime())
            ->orderBy('ca.deadline', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据审核类型查找记录
     */
    public function findByAuditType(string $auditType, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->where('ca.auditType = :auditType')
            ->setParameter('auditType', $auditType);

        if ((bool) $status) {
            $qb->andWhere('ca.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('ca.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 获取审核统计信息
     */
    public function getAuditStatistics(?Course $course = null): array
    {
        $qb = $this->createQueryBuilder('ca');

        if ((bool) $course) {
            $qb->where('ca.course = :course')
               ->setParameter('course', $course);
        }

        $totalAudits = $qb->select('COUNT(ca.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $pendingAudits = $qb->select('COUNT(ca.id)')
            ->andWhere('ca.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();

        $approvedAudits = $qb->select('COUNT(ca.id)')
            ->andWhere('ca.status = :status')
            ->setParameter('status', 'approved')
            ->getQuery()
            ->getSingleScalarResult();

        $rejectedAudits = $qb->select('COUNT(ca.id)')
            ->andWhere('ca.status = :status')
            ->setParameter('status', 'rejected')
            ->getQuery()
            ->getSingleScalarResult();

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
     */
    public function findByAuditor(string $auditor, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('ca')
            ->where('ca.auditor = :auditor')
            ->setParameter('auditor', $auditor);

        if ((bool) $status) {
            $qb->andWhere('ca.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('ca.createTime', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * 查找课程的最新审核记录
     */
    public function findLatestByCourse(Course $course): ?CourseAudit
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.course = :course')
            ->setParameter('course', $course)
            ->orderBy('ca.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找超时的审核记录
     * 
     * @param int $timeoutHours 超时小时数
     * @return CourseAudit[]
     */
    public function findTimeoutAudits(int $timeoutHours): array
    {
        $timeoutDate = new \DateTime();
        $timeoutDate->modify("-{$timeoutHours} hours");

        return $this->createQueryBuilder('ca')
            ->where('ca.status = :status')
            ->andWhere('ca.createTime < :timeoutDate')
            ->setParameter('status', 'pending')
            ->setParameter('timeoutDate', $timeoutDate)
            ->orderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找旧的审核记录（超过指定天数）
     * 
     * @param int $days 天数
     * @return CourseAudit[]
     */
    public function findOldAudits(int $days): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('ca')
            ->where('ca.createTime < :date')
            ->andWhere('ca.status != :pendingStatus')
            ->setParameter('date', $date)
            ->setParameter('pendingStatus', 'pending')
            ->orderBy('ca.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 