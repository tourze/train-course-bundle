<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CleanupTask;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CleanupSpecification\CourseCleanupSpecification;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程清理任务
 */
class CourseCleanupTask
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CourseRepository $courseRepository,
        private readonly CourseConfigService $configService,
        private readonly CourseCleanupSpecification $cleanupSpecification,
    ) {
    }

    /**
     * 清理过期的课程
     */
    public function cleanup(SymfonyStyle $io, bool $dryRun): int
    {
        $io->section('清理过期课程');

        $expiredCourses = $this->courseRepository->findExpiredCourses();
        $io->text(sprintf('找到 %d 个过期课程', count($expiredCourses)));

        $cleanedCount = 0;

        if (!$this->isAutoCleanupEnabled()) {
            $io->warning('自动清理过期课程功能未启用');

            return 0;
        }

        foreach ($expiredCourses as $course) {
            if (!$this->cleanupSpecification->shouldCleanupCourse($course)) {
                continue;
            }

            $this->logCourseDeletion($io, $course);

            if (!$dryRun) {
                $this->entityManager->remove($course);
                ++$cleanedCount;
            } else {
                ++$cleanedCount;
            }
        }

        if (!$dryRun && $cleanedCount > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('过期课程清理完成，共处理 %d 个课程', $cleanedCount));

        return $cleanedCount;
    }

    /**
     * 检查是否启用自动清理
     */
    private function isAutoCleanupEnabled(): bool
    {
        return (bool) $this->configService->get('course.auto_cleanup_expired', false);
    }

    /**
     * 记录课程删除信息
     */
    private function logCourseDeletion(SymfonyStyle $io, object $course): void
    {
        $id = method_exists($course, 'getId') ? $course->getId() : null;
        $courseId = (is_string($id) || is_numeric($id)) ? (string) $id : 'unknown';
        $title = method_exists($course, 'getTitle') ? $course->getTitle() : null;
        $courseTitle = (is_string($title) || is_numeric($title)) ? (string) $title : 'unknown';

        $io->text(sprintf('删除课程: %s (ID: %s)', $courseTitle, $courseId));
    }
}
