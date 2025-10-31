<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupUtil;

/**
 * 备份目录管理服务
 */
class BackupDirectoryManager
{
    /**
     * 创建备份目录
     */
    public function createBackupDirectory(string $basePath): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupDir = rtrim($basePath, '/') . "/course_backup_{$timestamp}";

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0o755, true);
        }

        return $backupDir;
    }

    /**
     * 删除目录
     */
    public function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
