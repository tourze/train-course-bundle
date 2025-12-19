<?php

namespace Tourze\TrainCourseBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程审核命令
 *
 * 自动化处理课程审核任务，包括超时检测、自动审核等功能
 */
#[AsCommand(name: self::NAME, description: '处理课程审核任务')]
final class CourseAuditCommand extends Command
{
    public const NAME = 'train-course:audit';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseRepository $courseRepository,
        private CourseAuditRepository $auditRepository,
        private CourseConfigService $configService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('auto-approve', null, InputOption::VALUE_NONE, '自动审核符合条件的课程')
            ->addOption('check-timeout', null, InputOption::VALUE_NONE, '检查审核超时的课程')
            ->addOption('course-id', null, InputOption::VALUE_OPTIONAL, '指定课程ID进行审核')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际执行操作')
            ->setHelp(<<<'TXT'
                该命令用于处理课程审核相关任务：

                <info>自动审核：</info>
                  <comment>php bin/console train-course:audit --auto-approve</comment>

                <info>检查超时：</info>
                  <comment>php bin/console train-course:audit --check-timeout</comment>

                <info>审核指定课程：</info>
                  <comment>php bin/console train-course:audit --course-id=123</comment>

                <info>试运行模式：</info>
                  <comment>php bin/console train-course:audit --auto-approve --dry-run</comment>
                TXT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('运行在试运行模式，不会实际执行操作');
        }

        $courseId = $input->getOption('course-id');
        $autoApprove = $input->getOption('auto-approve');
        $checkTimeout = $input->getOption('check-timeout');

        if (is_string($courseId) && '' !== $courseId) {
            return $this->auditSpecificCourse($io, $courseId, $dryRun);
        }

        if ((bool) $autoApprove) {
            return $this->autoApproveCourses($io, $dryRun);
        }

        if ((bool) $checkTimeout) {
            return $this->checkTimeoutAudits($io, $dryRun);
        }

        // 默认执行所有任务
        $io->title('执行课程审核任务');

        $this->autoApproveCourses($io, $dryRun);
        $this->checkTimeoutAudits($io, $dryRun);

        $io->success('课程审核任务执行完成');

        return Command::SUCCESS;
    }

    /**
     * 自动审核符合条件的课程
     */
    private function autoApproveCourses(SymfonyStyle $io, bool $dryRun): int
    {
        $io->section('自动审核课程');

        if (!$this->isAutoAuditEnabled()) {
            $io->warning('自动审核功能未启用');

            return Command::SUCCESS;
        }

        $pendingAudits = $this->auditRepository->findPendingAudits();
        $autoApprovedCount = $this->processAutoApprovalAudits($io, $pendingAudits, $dryRun);

        $this->saveAutoApprovalChanges($autoApprovedCount, $dryRun);
        $io->success(sprintf('自动审核完成，共处理 %d 个课程', $autoApprovedCount));

        return Command::SUCCESS;
    }

    /**
     * 检查审核超时的课程
     */
    private function checkTimeoutAudits(SymfonyStyle $io, bool $dryRun): int
    {
        $io->section('检查审核超时');

        $timeoutHours = $this->configService->get('course.audit_timeout_hours', 72);
        if (!is_int($timeoutHours)) {
            $timeoutHours = 72;
        }
        $timeoutAudits = $this->auditRepository->findTimeoutAudits($timeoutHours);
        $timeoutCount = $this->processTimeoutAudits($io, $timeoutAudits, $dryRun);

        $this->saveTimeoutChanges($timeoutCount, $dryRun);
        $io->success(sprintf('超时检查完成，共处理 %d 个超时审核', $timeoutCount));

        return Command::SUCCESS;
    }

    /**
     * 审核指定课程
     */
    private function auditSpecificCourse(SymfonyStyle $io, string $courseId, bool $dryRun): int
    {
        $io->section(sprintf('审核课程 ID: %s', $courseId));

        $course = $this->courseRepository->find($courseId);
        if (null === $course) {
            $io->error(sprintf('课程 ID %s 不存在', $courseId));

            return Command::FAILURE;
        }

        $audit = $this->auditRepository->findLatestByCourse($course);
        if (!$this->hasValidPendingAudit($audit)) {
            $io->warning(sprintf('课程 %s 没有待审核的记录', $course->getTitle()));

            return Command::SUCCESS;
        }

        // $audit is guaranteed to be non-null here due to hasValidPendingAudit check
        assert($audit instanceof CourseAudit);
        $this->displayCourseInfo($io, $course, $audit);
        $this->executeManualAudit($io, $audit, $dryRun);

        return Command::SUCCESS;
    }

    /**
     * 判断是否应该自动审核通过
     */
    private function shouldAutoApprove(CourseAudit $audit): bool
    {
        $course = $audit->getCourse();
        if (null === $course) {
            return false;
        }

        // 检查课程基础信息完整性
        $title = $course->getTitle();
        $description = $course->getDescription();
        if ('' === $title || '' === $description) {
            return false;
        }

        // 检查是否有章节和课时
        if (0 === $course->getChapterCount() || 0 === $course->getLessonCount()) {
            return false;
        }

        // 检查是否有封面图
        if ('' === $course->getCoverThumb() || null === $course->getCoverThumb()) {
            return false;
        }

        // 检查学时设置
        if (null === $course->getLearnHour() || $course->getLearnHour() <= 0) {
            return false;
        }

        // 检查价格设置
        if (null === $course->getPrice()) {
            return false;
        }

        // 检查审核类型
        /** @var array<string> $autoApproveTypes */
        $autoApproveTypes = $this->configService->get('course.auto_approve_types', ['update']);
        if (!in_array($audit->getAuditType(), $autoApproveTypes, true)) {
            return false;
        }

        return true;
    }

    /**
     * 检查自动审核是否启用
     */
    private function isAutoAuditEnabled(): bool
    {
        return (bool) $this->configService->get('course.auto_audit_enabled', false);
    }

    /**
     * 处理自动审核列表
     * @param array<CourseAudit> $pendingAudits
     */
    private function processAutoApprovalAudits(SymfonyStyle $io, array $pendingAudits, bool $dryRun): int
    {
        $autoApprovedCount = 0;

        foreach ($pendingAudits as $audit) {
            if ($this->shouldAutoApprove($audit)) {
                $this->logAutoApprovalAction($io, $audit);
                $this->executeAutoApproval($audit, $dryRun);
                ++$autoApprovedCount;
            }
        }

        return $autoApprovedCount;
    }

    /**
     * 记录自动审核操作
     */
    private function logAutoApprovalAction(SymfonyStyle $io, CourseAudit $audit): void
    {
        $course = $audit->getCourse();
        if (null === $course) {
            return;
        }

        $io->text(sprintf('自动审核通过课程: %s (ID: %s)',
            $course->getTitle(),
            $course->getId()
        ));
    }

    /**
     * 执行自动审核操作
     */
    private function executeAutoApproval(CourseAudit $audit, bool $dryRun): void
    {
        if (!$dryRun) {
            $audit->setStatus('approved');
            $audit->setAuditTime(new \DateTime());
            $audit->setAuditorId('system');
            $audit->setAuditComment('系统自动审核通过');
            $this->entityManager->persist($audit);
        }
    }

    /**
     * 保存自动审核变更
     */
    private function saveAutoApprovalChanges(int $autoApprovedCount, bool $dryRun): void
    {
        if (!$dryRun && $autoApprovedCount > 0) {
            $this->entityManager->flush();
        }
    }

    /**
     * 处理超时审核列表
     * @param array<CourseAudit> $timeoutAudits
     */
    private function processTimeoutAudits(SymfonyStyle $io, array $timeoutAudits, bool $dryRun): int
    {
        $timeoutCount = 0;

        foreach ($timeoutAudits as $audit) {
            $this->logTimeoutAction($io, $audit);
            $this->executeTimeoutAction($audit, $dryRun);
            ++$timeoutCount;
        }

        return $timeoutCount;
    }

    /**
     * 记录超时操作
     */
    private function logTimeoutAction(SymfonyStyle $io, CourseAudit $audit): void
    {
        $course = $audit->getCourse();
        $submitTime = $audit->getSubmitTime();

        if (null === $course || null === $submitTime) {
            return;
        }

        $io->text(sprintf('发现超时审核: %s (ID: %s, 提交时间: %s)',
            $course->getTitle(),
            $course->getId(),
            $submitTime->format('Y-m-d H:i:s')
        ));
    }

    /**
     * 执行超时处理操作
     */
    private function executeTimeoutAction(CourseAudit $audit, bool $dryRun): void
    {
        if (!$dryRun) {
            if ($this->shouldAutoRejectTimeout()) {
                $this->setAuditRejected($audit);
            } else {
                $this->reassignAuditor($audit);
            }
            $this->entityManager->persist($audit);
        }
    }

    /**
     * 检查是否应该自动拒绝超时审核
     */
    private function shouldAutoRejectTimeout(): bool
    {
        return (bool) $this->configService->get('course.auto_reject_timeout', false);
    }

    /**
     * 设置审核为拒绝状态
     */
    private function setAuditRejected(CourseAudit $audit): void
    {
        $audit->setStatus('rejected');
        $audit->setAuditTime(new \DateTime());
        $audit->setAuditorId('system');
        $audit->setAuditComment('审核超时，系统自动拒绝');
        $audit->setRejectReason('审核超时');
    }

    /**
     * 重新分配审核员
     */
    private function reassignAuditor(CourseAudit $audit): void
    {
        $audit->setAuditorId(null);
        $audit->setAuditComment('审核超时，重新分配审核员');
    }

    /**
     * 保存超时处理变更
     */
    private function saveTimeoutChanges(int $timeoutCount, bool $dryRun): void
    {
        if (!$dryRun && $timeoutCount > 0) {
            $this->entityManager->flush();
        }
    }

    /**
     * 检查是否有有效的待审核记录
     */
    private function hasValidPendingAudit(?CourseAudit $audit): bool
    {
        return null !== $audit && 'pending' === $audit->getStatus();
    }

    /**
     * 显示课程信息
     */
    private function displayCourseInfo(SymfonyStyle $io, Course $course, CourseAudit $audit): void
    {
        $submitTime = $audit->getSubmitTime();
        if (null === $submitTime) {
            return;
        }

        $io->text(sprintf('课程信息: %s', $course->getTitle()));
        $io->text(sprintf('提交时间: %s', $submitTime->format('Y-m-d H:i:s')));
        $io->text(sprintf('审核类型: %s', $audit->getAuditType()));

        if ($this->shouldAutoApprove($audit)) {
            $io->text('符合自动审核条件，建议通过');
        } else {
            $io->text('需要人工审核');
        }
    }

    /**
     * 执行手动审核
     */
    private function executeManualAudit(SymfonyStyle $io, CourseAudit $audit, bool $dryRun): void
    {
        if (!$dryRun) {
            $action = $io->choice('请选择操作', ['approve', 'reject', 'skip'], 'skip');

            if ('approve' === $action) {
                $this->approveAudit($audit);
                $this->entityManager->flush();
                $io->success('课程审核通过');
            } elseif ('reject' === $action) {
                $reason = $io->ask('请输入拒绝原因');
                if (!is_string($reason)) {
                    $reason = '';
                }
                $this->rejectAudit($audit, $reason);
                $this->entityManager->flush();
                $io->success('课程审核拒绝');
            } else {
                $io->text('跳过审核');
            }
        }
    }

    /**
     * 审核通过
     */
    private function approveAudit(CourseAudit $audit): void
    {
        $audit->setStatus('approved');
        $audit->setAuditTime(new \DateTime());
        $audit->setAuditorId('admin');
        $audit->setAuditComment('命令行审核通过');
        $this->entityManager->persist($audit);
    }

    /**
     * 审核拒绝
     */
    private function rejectAudit(CourseAudit $audit, string $reason): void
    {
        $audit->setStatus('rejected');
        $audit->setAuditTime(new \DateTime());
        $audit->setAuditorId('admin');
        $audit->setAuditComment('命令行审核拒绝');
        $audit->setRejectReason($reason);
        $this->entityManager->persist($audit);
    }
}
