<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\CleanupTask;

use Symfony\Component\Console\Input\InputInterface;

/**
 * 清理选项解析器
 */
class CleanupOptionsParser
{
    /**
     * 解析清理选项
     * @return array<string, bool>
     */
    public function parseCleanupOptions(InputInterface $input): array
    {
        $clearCache = (bool) $input->getOption('clear-cache');
        $cleanupVersions = (bool) $input->getOption('cleanup-versions');
        $cleanupAudits = (bool) $input->getOption('cleanup-audits');
        $cleanupExpired = (bool) $input->getOption('cleanup-expired');

        // 如果没有指定任何选项，执行所有清理任务
        if (!$clearCache && !$cleanupVersions && !$cleanupAudits && !$cleanupExpired) {
            return [
                'clearCache' => true,
                'cleanupVersions' => true,
                'cleanupAudits' => true,
                'cleanupExpired' => true,
            ];
        }

        return [
            'clearCache' => $clearCache,
            'cleanupVersions' => $cleanupVersions,
            'cleanupAudits' => $cleanupAudits,
            'cleanupExpired' => $cleanupExpired,
        ];
    }
}
