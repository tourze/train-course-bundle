<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupUtil;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 备份压缩服务
 */
class BackupCompressor
{
    public function __construct(
        private readonly BackupDirectoryManager $directoryManager,
    ) {
    }

    /**
     * 压缩备份文件
     */
    public function compressBackup(string $backupDir, SymfonyStyle $io): string
    {
        $io->section('压缩备份文件');

        $archivePath = $backupDir . '.tar.gz';

        // 使用tar命令压缩
        $command = "tar -czf {$archivePath} -C " . dirname($backupDir) . ' ' . basename($backupDir);
        exec($command, $output, $returnCode);

        if (0 === $returnCode) {
            $io->success("备份文件已压缩: {$archivePath}");
            // 删除原始目录
            $this->directoryManager->removeDirectory($backupDir);
        } else {
            $io->warning('压缩失败，保留原始备份目录');
        }

        return $archivePath;
    }
}
