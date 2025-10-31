<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CleanupTask;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;

/**
 * 版本清理任务
 */
class VersionCleanupTask
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CourseVersionRepository $versionRepository,
    ) {
    }

    /**
     * 清理过期的课程版本
     */
    public function cleanup(SymfonyStyle $io, int $days, bool $dryRun): int
    {
        $io->section(sprintf('清理 %d 天前的课程版本', $days));

        $oldVersions = $this->versionRepository->findOldVersions($days);

        $io->text(sprintf('找到 %d 个过期版本', count($oldVersions)));

        $cleanedCount = 0;

        foreach ($oldVersions as $version) {
            if ($this->shouldSkipVersion($version)) {
                continue;
            }

            $this->logVersionDeletion($io, $version);

            if (!$dryRun) {
                $this->entityManager->remove($version);
                ++$cleanedCount;
            } else {
                ++$cleanedCount;
            }
        }

        if (!$dryRun && $cleanedCount > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('版本清理完成，共删除 %d 个过期版本', $cleanedCount));

        return $cleanedCount;
    }

    /**
     * 检查是否应该跳过版本
     */
    private function shouldSkipVersion(object $version): bool
    {
        $status = method_exists($version, 'getStatus') ? $version->getStatus() : null;

        // 保留当前版本和已发布版本
        return 'current' === $status || 'published' === $status;
    }

    /**
     * 记录版本删除信息
     */
    private function logVersionDeletion(SymfonyStyle $io, object $version): void
    {
        $course = method_exists($version, 'getCourse') ? $version->getCourse() : null;
        $createTime = method_exists($version, 'getCreateTime') ? $version->getCreateTime() : null;
        $versionNumber = method_exists($version, 'getVersion') ? $version->getVersion() : 'unknown';

        if (null === $course || null === $createTime) {
            return;
        }

        $title = (is_object($course) && method_exists($course, 'getTitle')) ? $course->getTitle() : null;
        $courseTitle = (is_string($title) || is_numeric($title)) ? (string) $title : 'unknown';
        $createTimeFormatted = ($createTime instanceof \DateTimeInterface) ? $createTime->format('Y-m-d H:i:s') : 'unknown';

        $io->text(sprintf('删除版本: 课程 %s, 版本 %s, 创建时间 %s',
            $courseTitle,
            is_string($versionNumber) || is_numeric($versionNumber) ? (string) $versionNumber : 'unknown',
            $createTimeFormatted
        ));
    }
}
