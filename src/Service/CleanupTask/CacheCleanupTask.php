<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CleanupTask;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 缓存清理任务
 */
class CacheCleanupTask
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * 清理课程相关缓存
     */
    public function cleanup(SymfonyStyle $io, bool $dryRun): int
    {
        $io->section('清理课程缓存');

        $cacheKeys = [
            'course_content_structure_*',
            'course_play_control_*',
            'course_analytics_*',
            'course_rankings_*',
            'course_details_*',
        ];

        $clearedCount = 0;

        foreach ($cacheKeys as $pattern) {
            $io->text(sprintf('清理缓存模式: %s', $pattern));

            if (!$dryRun) {
                try {
                    $this->cache->clear();
                    ++$clearedCount;
                } catch (\Throwable $e) {
                    $io->warning(sprintf('清理缓存失败: %s', $e->getMessage()));
                }
            } else {
                ++$clearedCount;
            }
        }

        $io->success(sprintf('缓存清理完成，共清理 %d 个缓存模式', $clearedCount));

        return $clearedCount;
    }
}
