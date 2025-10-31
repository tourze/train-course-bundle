<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 备份策略接口
 */
interface BackupStrategyInterface
{
    /**
     * 执行备份操作
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    public function backup(string $backupDir, bool $includeMedia, SymfonyStyle $io): array;
}
