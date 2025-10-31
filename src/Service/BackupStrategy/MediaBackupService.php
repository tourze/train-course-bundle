<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 媒体文件备份服务
 */
class MediaBackupService
{
    /**
     * 备份媒体文件
     * @param array<object> $courses
     */
    public function backupMediaFiles(string $backupDir, array $courses, SymfonyStyle $io): int
    {
        $io->section('备份媒体文件');

        $mediaDir = $backupDir . '/media';
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0o755, true);
        }

        $totalSize = 0;

        // 这里应该根据实际的媒体文件存储方式来实现
        // 简化实现
        $io->info('媒体文件备份功能待实现');

        return $totalSize;
    }

    /**
     * 如果需要则备份媒体文件
     * @param array<object> $courses
     */
    public function backupMediaFilesIfRequired(string $backupDir, array $courses, bool $includeMedia, SymfonyStyle $io): int
    {
        if (!$includeMedia) {
            return 0;
        }

        return $this->backupMediaFiles($backupDir, $courses, $io);
    }
}
