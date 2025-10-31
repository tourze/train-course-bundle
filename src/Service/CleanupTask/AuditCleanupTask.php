<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CleanupTask;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;

/**
 * 审核记录清理任务
 */
class AuditCleanupTask
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CourseAuditRepository $auditRepository,
    ) {
    }

    /**
     * 清理过期的审核记录
     */
    public function cleanup(SymfonyStyle $io, int $days, bool $dryRun): int
    {
        $io->section(sprintf('清理 %d 天前的审核记录', $days));

        $oldAudits = $this->auditRepository->findOldAudits($days);

        $io->text(sprintf('找到 %d 个过期审核记录', count($oldAudits)));

        $cleanedCount = 0;

        foreach ($oldAudits as $audit) {
            if ($this->shouldSkipAudit($audit)) {
                continue;
            }

            $this->logAuditDeletion($io, $audit);

            if (!$dryRun) {
                $this->entityManager->remove($audit);
                ++$cleanedCount;
            } else {
                ++$cleanedCount;
            }
        }

        if (!$dryRun && $cleanedCount > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('审核记录清理完成，共删除 %d 个过期记录', $cleanedCount));

        return $cleanedCount;
    }

    /**
     * 检查是否应该跳过审核记录
     */
    private function shouldSkipAudit(object $audit): bool
    {
        $status = method_exists($audit, 'getStatus') ? $audit->getStatus() : null;

        // 只删除已完成的审核记录（通过或拒绝）
        return 'pending' === $status;
    }

    /**
     * 记录审核删除信息
     */
    private function logAuditDeletion(SymfonyStyle $io, object $audit): void
    {
        $course = method_exists($audit, 'getCourse') ? $audit->getCourse() : null;
        $auditTime = method_exists($audit, 'getAuditTime') ? $audit->getAuditTime() : null;
        $status = method_exists($audit, 'getStatus') ? $audit->getStatus() : 'unknown';

        if (null === $course) {
            return;
        }

        $title = (is_object($course) && method_exists($course, 'getTitle')) ? $course->getTitle() : null;
        $courseTitle = (is_string($title) || is_numeric($title)) ? (string) $title : 'unknown';
        $auditTimeFormatted = ($auditTime instanceof \DateTimeInterface) ? $auditTime->format('Y-m-d H:i:s') : '未审核';

        $io->text(sprintf('删除审核记录: 课程 %s, 状态 %s, 审核时间 %s',
            $courseTitle,
            is_string($status) ? $status : 'unknown',
            $auditTimeFormatted
        ));
    }
}
