<?php

namespace Tourze\TrainCourseBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Entity\CourseAudit;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * 课程审核命令
 *
 * 自动化处理课程审核任务，包括超时检测、自动审核等功能
 */
#[AsCommand(
    name: self::NAME,
    description: '处理课程审核任务'
)]
class CourseAuditCommand extends Command
{
    public const NAME = 'train-course:audit';
public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseRepository $courseRepository,
        private CourseAuditRepository $auditRepository,
        private CourseConfigService $configService
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
            ->setHelp('
该命令用于处理课程审核相关任务：

<info>自动审核：</info>
  <comment>php bin/console train-course:audit --auto-approve</comment>

<info>检查超时：</info>
  <comment>php bin/console train-course:audit --check-timeout</comment>

<info>审核指定课程：</info>
  <comment>php bin/console train-course:audit --course-id=123</comment>

<info>试运行模式：</info>
  <comment>php bin/console train-course:audit --auto-approve --dry-run</comment>
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if ((bool) $dryRun) {
            $io->note('运行在试运行模式，不会实际执行操作');
        }

        $courseId = $input->getOption('course-id');
        $autoApprove = $input->getOption('auto-approve');
        $checkTimeout = $input->getOption('check-timeout');

        if ((bool) $courseId) {
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

        if ((bool) $this->configService->get('course.auto_audit_enabled', false) === false) {
            $io->warning('自动审核功能未启用');
            return Command::SUCCESS;
        }

        $pendingAudits = $this->auditRepository->findPendingAudits();
        $autoApprovedCount = 0;

        foreach ($pendingAudits as $audit) {
            if ($this->shouldAutoApprove($audit)) {
                $io->text(sprintf('自动审核通过课程: %s (ID: %s)', 
                    $audit->getCourse()->getTitle(), 
                    $audit->getCourse()->getId()
                ));

                if (!$dryRun) {
                    $audit->setStatus('approved');
                    $audit->setAuditTime(new \DateTime());
                    $audit->setAuditorId('system');
                    $audit->setAuditComment('系统自动审核通过');
                    
                    $this->entityManager->persist($audit);
                }

                $autoApprovedCount++;
            }
        }

        if (!$dryRun && $autoApprovedCount > 0) {
            $this->entityManager->flush();
        }

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
        $timeoutAudits = $this->auditRepository->findTimeoutAudits($timeoutHours);
        $timeoutCount = 0;

        foreach ($timeoutAudits as $audit) {
            $io->text(sprintf('发现超时审核: %s (ID: %s, 提交时间: %s)', 
                $audit->getCourse()->getTitle(), 
                $audit->getCourse()->getId(),
                $audit->getSubmitTime()->format('Y-m-d H:i:s')
            ));

            if (!$dryRun) {
                // 可以选择自动拒绝或重新分配审核员
                if ((bool) $this->configService->get('course.auto_reject_timeout', false)) {
                    $audit->setStatus('rejected');
                    $audit->setAuditTime(new \DateTime());
                    $audit->setAuditorId('system');
                    $audit->setAuditComment('审核超时，系统自动拒绝');
                    $audit->setRejectReason('审核超时');
                } else {
                    // 重新分配审核员
                    $audit->setAuditorId(null);
                    $audit->setAuditComment('审核超时，重新分配审核员');
                }
                
                $this->entityManager->persist($audit);
            }

            $timeoutCount++;
        }

        if (!$dryRun && $timeoutCount > 0) {
            $this->entityManager->flush();
        }

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
        if ($course === null) {
            $io->error(sprintf('课程 ID %s 不存在', $courseId));
            return Command::FAILURE;
        }

        $audit = $this->auditRepository->findLatestByCourse($course);
        if ($audit === null || $audit->getStatus() !== 'pending') {
            $io->warning(sprintf('课程 %s 没有待审核的记录', $course->getTitle()));
            return Command::SUCCESS;
        }

        $io->text(sprintf('课程信息: %s', $course->getTitle()));
        $io->text(sprintf('提交时间: %s', $audit->getSubmitTime()->format('Y-m-d H:i:s')));
        $io->text(sprintf('审核类型: %s', $audit->getAuditType()));

        if ($this->shouldAutoApprove($audit)) {
            $io->text('符合自动审核条件，建议通过');
        } else {
            $io->text('需要人工审核');
        }

        if (!$dryRun) {
            $action = $io->choice('请选择操作', ['approve', 'reject', 'skip'], 'skip');
            
            if ($action === 'approve') {
                $audit->setStatus('approved');
                $audit->setAuditTime(new \DateTime());
                $audit->setAuditorId('admin');
                $audit->setAuditComment('命令行审核通过');
                
                $this->entityManager->persist($audit);
                $this->entityManager->flush();
                
                $io->success('课程审核通过');
            } elseif ($action === 'reject') {
                $reason = $io->ask('请输入拒绝原因');
                
                $audit->setStatus('rejected');
                $audit->setAuditTime(new \DateTime());
                $audit->setAuditorId('admin');
                $audit->setAuditComment('命令行审核拒绝');
                $audit->setRejectReason($reason);
                
                $this->entityManager->persist($audit);
                $this->entityManager->flush();
                
                $io->success('课程审核拒绝');
            } else {
                $io->text('跳过审核');
            }
        }

        return Command::SUCCESS;
    }

    /**
     * 判断是否应该自动审核通过
     */
    private function shouldAutoApprove(CourseAudit $audit): bool
    {
        $course = $audit->getCourse();

        // 检查课程基础信息完整性
        if (empty($course->getTitle()) || empty($course->getDescription())) {
            return false;
        }

        // 检查是否有章节和课时
        if ($course->getChapterCount() === 0 || $course->getLessonCount() === 0) {
            return false;
        }

        // 检查是否有封面图
        if (empty($course->getCoverThumb())) {
            return false;
        }

        // 检查学时设置
        if ($course->getLearnHour() === null || $course->getLearnHour() <= 0) {
            return false;
        }

        // 检查价格设置
        if ($course->getPrice() === null) {
            return false;
        }

        // 检查审核类型
        $autoApproveTypes = $this->configService->get('course.auto_approve_types', ['update']);
        if (!in_array($audit->getAuditType(), $autoApproveTypes)) {
            return false;
        }

        return true;
    }
} 