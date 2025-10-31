<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

/**
 * 备份结果构建服务
 */
class BackupResultBuilder
{
    /**
     * 构建空备份结果
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    public function buildEmptyBackupResult(): array
    {
        return [
            'course_count' => 0,
            'chapter_count' => 0,
            'lesson_count' => 0,
            'total_size' => 0,
        ];
    }

    /**
     * 构建备份结果
     * @param array{chapter_count: int, lesson_count: int} $stats
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    public function buildBackupResult(int $courseCount, array $stats, string $dataFile, int $mediaSize): array
    {
        $fileSize = filesize($dataFile);
        if (false === $fileSize) {
            $fileSize = 0;
        }

        return [
            'course_count' => $courseCount,
            'chapter_count' => $stats['chapter_count'],
            'lesson_count' => $stats['lesson_count'],
            'total_size' => $fileSize + $mediaSize,
        ];
    }
}
