<?php

declare(strict_types=1);

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
use Tourze\TrainCourseBundle\Service\CleanupSpecification\CourseCleanupSpecification;
use Tourze\TrainCourseBundle\Service\CleanupTask\AuditCleanupTask;
use Tourze\TrainCourseBundle\Service\CleanupTask\CacheCleanupTask;
use Tourze\TrainCourseBundle\Service\CleanupTask\CleanupOptionsParser;
use Tourze\TrainCourseBundle\Service\CleanupTask\CourseCleanupTask;
use Tourze\TrainCourseBundle\Service\CleanupTask\VersionCleanupTask;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程清理命令
 *
 * 清理过期数据、缓存和临时文件，维护系统性能
 */
#[AsCommand(name: self::NAME, description: '清理课程相关的过期数据和缓存')]
class CourseCleanupCommand extends Command
{
    public const NAME = 'train-course:cleanup';

    public function __construct(
        private readonly CacheCleanupTask $cacheCleanupTask,
        private readonly VersionCleanupTask $versionCleanupTask,
        private readonly AuditCleanupTask $auditCleanupTask,
        private readonly CourseCleanupTask $courseCleanupTask,
        private readonly CleanupOptionsParser $optionsParser,
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
            ->setHelp(<<<'TXT'
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
                TXT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $daysOption = $input->getOption('days');
        $days = is_numeric($daysOption) ? (int) $daysOption : 30;
        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('运行在试运行模式，不会实际执行操作');
        }

        $options = $this->optionsParser->parseCleanupOptions($input);
        $io->title('课程数据清理任务');

        $totalCleaned = $this->executeCleanupTasks($io, $options, $days, $dryRun);
        $io->success(sprintf('清理任务完成，共清理 %d 项数据', $totalCleaned));

        return Command::SUCCESS;
    }

    /**
     * 执行清理任务
     * @param array<string, bool> $options
     */
    private function executeCleanupTasks(SymfonyStyle $io, array $options, int $days, bool $dryRun): int
    {
        $totalCleaned = 0;

        if ($options['clearCache']) {
            $totalCleaned += $this->cacheCleanupTask->cleanup($io, $dryRun);
        }

        if ($options['cleanupVersions']) {
            $totalCleaned += $this->versionCleanupTask->cleanup($io, $days, $dryRun);
        }

        if ($options['cleanupAudits']) {
            $totalCleaned += $this->auditCleanupTask->cleanup($io, $days, $dryRun);
        }

        if ($options['cleanupExpired']) {
            $totalCleaned += $this->courseCleanupTask->cleanup($io, $dryRun);
        }

        return $totalCleaned;
    }
}
