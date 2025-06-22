<?php

namespace Tourze\TrainCourseBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程清理命令
 * 
 * 清理过期数据、缓存和临时文件，维护系统性能
 */
#[AsCommand(
    name: self::NAME,
    description: '清理课程相关的过期数据和缓存'
)]
class CourseCleanupCommand extends Command
{
    
    public const NAME = 'train-course:cleanup';
public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache,
        private readonly CourseRepository $courseRepository,
        private readonly CourseVersionRepository $versionRepository,
        private readonly CourseAuditRepository $auditRepository,
        private readonly CourseConfigService $configService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('clear-cache', null, InputOption::VALUE_NONE, '清理课程相关缓存')
            ->addOption('cleanup-versions', null, InputOption::VALUE_NONE, '清理过期的课程版本')
            ->addOption('cleanup-audits', null, InputOption::VALUE_NONE, '清理过期的审核记录')
            ->addOption('cleanup-expired', null, InputOption::VALUE_NONE, '清理过期的课程')
            ->addOption('days', null, InputOption::VALUE_OPTIONAL, '保留天数（默认30天）', 30)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际执行操作')
            ->setHelp('
该命令用于清理课程相关的过期数据：

<info>清理缓存：</info>
  <comment>php bin/console train-course:cleanup --clear-cache</comment>

<info>清理过期版本：</info>
  <comment>php bin/console train-course:cleanup --cleanup-versions --days=30</comment>

<info>清理审核记录：</info>
  <comment>php bin/console train-course:cleanup --cleanup-audits --days=90</comment>

<info>清理过期课程：</info>
  <comment>php bin/console train-course:cleanup --cleanup-expired</comment>

<info>全面清理：</info>
  <comment>php bin/console train-course:cleanup</comment>

<info>试运行模式：</info>
  <comment>php bin/console train-course:cleanup --dry-run</comment>
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $days = (int) $input->getOption('days');

        if ((bool) $dryRun) {
            $io->note('运行在试运行模式，不会实际执行操作');
        }

        $clearCache = (bool) $input->getOption('clear-cache');
        $cleanupVersions = (bool) $input->getOption('cleanup-versions');
        $cleanupAudits = (bool) $input->getOption('cleanup-audits');
        $cleanupExpired = (bool) $input->getOption('cleanup-expired');

        // 如果没有指定任何选项，执行所有清理任务
        if (!$clearCache && !$cleanupVersions && !$cleanupAudits && !$cleanupExpired) {
            $clearCache = $cleanupVersions = $cleanupAudits = $cleanupExpired = true;
        }

        $io->title('课程数据清理任务');

        $totalCleaned = 0;

        if ((bool) $clearCache) {
            $totalCleaned += $this->clearCourseCache($io, $dryRun);
        }

        if ((bool) $cleanupVersions) {
            $totalCleaned += $this->cleanupOldVersions($io, $days, $dryRun);
        }

        if ((bool) $cleanupAudits) {
            $totalCleaned += $this->cleanupOldAudits($io, $days, $dryRun);
        }

        if ((bool) $cleanupExpired) {
            $totalCleaned += $this->cleanupExpiredCourses($io, $dryRun);
        }

        $io->success(sprintf('清理任务完成，共清理 %d 项数据', $totalCleaned));
        return Command::SUCCESS;
    }

    /**
     * 清理课程相关缓存
     */
    private function clearCourseCache(SymfonyStyle $io, bool $dryRun): int
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
                // 这里需要根据实际的缓存实现来清理
                // 由于 PSR-6 没有通配符删除，这里使用简化实现
                try {
                    $this->cache->clear();
                    $clearedCount++;
                } catch (\Throwable $e) {
                    $io->warning(sprintf('清理缓存失败: %s', $e->getMessage()));
                }
            } else {
                $clearedCount++;
            }
        }

        $io->success(sprintf('缓存清理完成，共清理 %d 个缓存模式', $clearedCount));
        return $clearedCount;
    }

    /**
     * 清理过期的课程版本
     */
    private function cleanupOldVersions(SymfonyStyle $io, int $days, bool $dryRun): int
    {
        $io->section(sprintf('清理 %d 天前的课程版本', $days));

        $oldVersions = $this->versionRepository->findOldVersions($days);

        $io->text(sprintf('找到 %d 个过期版本', count($oldVersions)));

        $cleanedCount = 0;

        foreach ($oldVersions as $version) {
            // 保留当前版本和已发布版本
            if ($version->getStatus() === 'current' || $version->getStatus() === 'published') {
                continue;
            }

            $io->text(sprintf('删除版本: 课程 %s, 版本 %s, 创建时间 %s',
                $version->getCourse()->getTitle(),
                $version->getVersion(),
                $version->getCreateTime()->format('Y-m-d H:i:s')
            ));

            if (!$dryRun) {
                $this->entityManager->remove($version);
                $cleanedCount++;
            } else {
                $cleanedCount++;
            }
        }

        if (!$dryRun && $cleanedCount > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('版本清理完成，共删除 %d 个过期版本', $cleanedCount));
        return $cleanedCount;
    }

    /**
     * 清理过期的审核记录
     */
    private function cleanupOldAudits(SymfonyStyle $io, int $days, bool $dryRun): int
    {
        $io->section(sprintf('清理 %d 天前的审核记录', $days));

        $oldAudits = $this->auditRepository->findOldAudits($days);

        $io->text(sprintf('找到 %d 个过期审核记录', count($oldAudits)));

        $cleanedCount = 0;

        foreach ($oldAudits as $audit) {
            // 只删除已完成的审核记录（通过或拒绝）
            if ($audit->getStatus() === 'pending') {
                continue;
            }

            $io->text(sprintf('删除审核记录: 课程 %s, 状态 %s, 审核时间 %s',
                $audit->getCourse()->getTitle(),
                $audit->getStatus(),
                $audit->getAuditTime()?->format('Y-m-d H:i:s') ?? '未审核'
            ));

            if (!$dryRun) {
                $this->entityManager->remove($audit);
                $cleanedCount++;
            } else {
                $cleanedCount++;
            }
        }

        if (!$dryRun && $cleanedCount > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('审核记录清理完成，共删除 %d 个过期记录', $cleanedCount));
        return $cleanedCount;
    }

    /**
     * 清理过期的课程
     */
    private function cleanupExpiredCourses(SymfonyStyle $io, bool $dryRun): int
    {
        $io->section('清理过期课程');

        $expiredCourses = $this->courseRepository->findExpiredCourses();
        $io->text(sprintf('找到 %d 个过期课程', count($expiredCourses)));

        $cleanedCount = 0;
        $autoCleanup = (bool) $this->configService->get('course.auto_cleanup_expired', false);

        if (!$autoCleanup) {
            $io->warning('自动清理过期课程功能未启用');
            return 0;
        }

        foreach ($expiredCourses as $course) {
            // 检查课程是否真的过期且无人学习
            if (!$this->shouldCleanupCourse($course)) {
                continue;
            }

            $io->text(sprintf('删除课程: %s (ID: %s)',
                $course->getTitle(),
                $course->getId()
            ));

            if (!$dryRun) {
                // 物理删除课程
                $this->entityManager->remove($course);
                $cleanedCount++;
            } else {
                $cleanedCount++;
            }
        }

        if (!$dryRun && $cleanedCount > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('过期课程清理完成，共处理 %d 个课程', $cleanedCount));
        return $cleanedCount;
    }

    /**
     * 判断课程是否应该被清理
     */
    private function shouldCleanupCourse($course): bool
    {
        // 检查课程是否无效
        if ($course->isValid()) {
            return false;
        }

        // 检查是否有学习记录（这里需要根据实际的学习记录实体来实现）
        // 暂时根据其他条件判断
        
        // 检查是否有收藏或评价
        if ($course->getCollects()->count() > 0 || $course->getEvaluates()->count() > 0) {
            return false;
        }

        // 检查课程创建时间，避免删除新创建的课程
        $gracePeriod = $this->configService->get('course.cleanup_grace_period_days', 7);
        $graceDate = new \DateTime(sprintf('-%d days', $gracePeriod));
        
        if ($course->getCreateTime() > $graceDate) {
            return false;
        }

        return true;
    }

} 