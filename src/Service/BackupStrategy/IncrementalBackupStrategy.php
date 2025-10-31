<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseRepository;

/**
 * 增量备份策略
 */
class IncrementalBackupStrategy implements BackupStrategyInterface
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly CourseSerializer $courseSerializer,
        private readonly MediaBackupService $mediaBackupService,
        private readonly CourseDataSaver $dataSaver,
        private readonly CourseStatisticsCalculator $statisticsCalculator,
        private readonly BackupResultBuilder $resultBuilder,
    ) {
    }

    /**
     * @return array{course_count: int, chapter_count: int, lesson_count: int, total_size: int}
     */
    public function backup(string $backupDir, bool $includeMedia, SymfonyStyle $io): array
    {
        $io->section('执行增量备份');

        $courses = $this->findCoursesUpdatedSince($io);

        if ($this->isEmptyBackup($courses, $io)) {
            return $this->resultBuilder->buildEmptyBackupResult();
        }

        $courseData = $this->courseSerializer->serializeCourses($courses);
        $stats = $this->statisticsCalculator->calculateCourseStatistics($courseData);

        // 保存增量数据
        $dataFile = $this->dataSaver->saveIncrementalData($backupDir, $courseData);

        // 备份媒体文件
        $mediaSize = $this->mediaBackupService->backupMediaFilesIfRequired($backupDir, $courses, $includeMedia, $io);

        return $this->resultBuilder->buildBackupResult(count($courses), $stats, $dataFile, $mediaSize);
    }

    /**
     * @return array<object>
     */
    private function findCoursesUpdatedSince(SymfonyStyle $io): array
    {
        // 这里需要根据实际的增量备份需求来实现
        // 暂时返回所有课程作为示例
        $courses = $this->courseRepository->findAll();
        $io->info('找到 ' . count($courses) . ' 个更新的课程');

        return $courses;
    }

    /**
     * @param array<object> $courses
     */
    private function isEmptyBackup(array $courses, SymfonyStyle $io): bool
    {
        if (0 === count($courses)) {
            $io->warning('没有找到需要备份的课程');

            return true;
        }

        return false;
    }
}
