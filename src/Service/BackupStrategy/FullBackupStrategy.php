<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseRepository;

/**
 * 全量备份策略
 */
class FullBackupStrategy implements BackupStrategyInterface
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly CourseSerializer $courseSerializer,
        private readonly MediaBackupService $mediaBackupService,
        private readonly CourseDataSaver $dataSaver,
        private readonly CourseStatisticsCalculator $statisticsCalculator,
    ) {
    }

    /**
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    public function backup(string $backupDir, bool $includeMedia, SymfonyStyle $io): array
    {
        $io->section('执行全量备份');

        $courses = $this->courseRepository->findAll();
        $courseData = $this->courseSerializer->serializeCoursesWithProgress($courses, $io);
        $stats = $this->statisticsCalculator->calculateCourseStatistics($courseData);

        // 保存课程数据
        $dataFile = $this->dataSaver->saveCourseData($backupDir, $courseData, 'courses.json');

        // 备份媒体文件
        $mediaSize = $this->mediaBackupService->backupMediaFilesIfRequired($backupDir, $courses, $includeMedia, $io);

        return $this->buildBackupResult(count($courses), $stats, $dataFile, $mediaSize);
    }

    /**
     * @param array{chapter_count: int, lesson_count: int} $stats
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    private function buildBackupResult(int $courseCount, array $stats, string $dataFile, int $mediaSize): array
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
