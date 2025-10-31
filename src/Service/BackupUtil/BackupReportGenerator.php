<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupUtil;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 备份报告生成服务
 */
class BackupReportGenerator
{
    /**
     * 生成备份报告
     * @param array<string, mixed> $backupInfo
     */
    public function generateBackupReport(string $backupDir, array $backupInfo, SymfonyStyle $io): void
    {
        $reportFile = $backupDir . '/backup_report.txt';

        $courseCount = is_int($backupInfo['course_count']) ? $backupInfo['course_count'] : 0;
        $chapterCount = is_int($backupInfo['chapter_count']) ? $backupInfo['chapter_count'] : 0;
        $lessonCount = is_int($backupInfo['lesson_count']) ? $backupInfo['lesson_count'] : 0;
        $totalSize = is_int($backupInfo['total_size']) ? $backupInfo['total_size'] : 0;

        $report = [
            '课程备份报告',
            '=' . str_repeat('=', 20),
            '备份时间: ' . date('Y-m-d H:i:s'),
            "备份目录: {$backupDir}",
            "课程数量: {$courseCount}",
            "章节数量: {$chapterCount}",
            "课时数量: {$lessonCount}",
            '文件大小: ' . $this->formatBytes($totalSize),
            '',
            '备份完成',
        ];

        file_put_contents($reportFile, implode("\n", $report));
        $io->info("备份报告已生成: {$reportFile}");
    }

    /**
     * 格式化字节大小
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes > 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * (int) $pow));

        return round($bytes, 2) . ' ' . $units[(int) $pow];
    }
}
