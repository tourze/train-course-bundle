<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CleanupTask;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Service\CleanupTask\AuditCleanupTask;

/**
 * @internal
 */
#[CoversClass(AuditCleanupTask::class)]
final class AuditCleanupTaskTest extends TestCase
{
    private AuditCleanupTask $task;

    private EntityManagerInterface $entityManager;

    private CourseAuditRepository $auditRepository;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->auditRepository = $this->createMock(CourseAuditRepository::class);
        $this->task = new AuditCleanupTask($this->entityManager, $this->auditRepository);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(AuditCleanupTask::class, $this->task);
    }

    public function testCleanupWithNoOldAudits(): void
    {
        $this->auditRepository->method('findOldAudits')
            ->willReturn([])
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(0, $result);
    }

    public function testCleanupWithOldAuditsInDryRun(): void
    {
        $audits = [
            $this->createApprovedAudit(),
            $this->createRejectedAudit(),
        ];

        $this->auditRepository->method('findOldAudits')
            ->willReturn($audits)
        ;

        $this->entityManager->expects($this->never())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, 30, true);

        $this->assertSame(2, $result);
    }

    public function testCleanupWithOldAuditsNotInDryRun(): void
    {
        $audits = [
            $this->createApprovedAudit(),
            $this->createRejectedAudit(),
        ];

        $this->auditRepository->method('findOldAudits')
            ->willReturn($audits)
        ;

        $this->entityManager->expects($this->exactly(2))
            ->method('remove')
        ;

        $this->entityManager->expects($this->once())
            ->method('flush')
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(2, $result);
    }

    public function testCleanupSkipsPendingAudits(): void
    {
        $audits = [
            $this->createPendingAudit(),
            $this->createApprovedAudit(),
        ];

        $this->auditRepository->method('findOldAudits')
            ->willReturn($audits)
        ;

        $this->entityManager->expects($this->once())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(1, $result);
    }

    public function testCleanupDoesNotFlushWhenNoAuditsDeleted(): void
    {
        $audits = [
            $this->createPendingAudit(),
        ];

        $this->auditRepository->method('findOldAudits')
            ->willReturn($audits)
        ;

        $this->entityManager->expects($this->never())
            ->method('flush')
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(0, $result);
    }

    /**
     * 创建已批准的审核
     */
    private function createApprovedAudit(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'approved';
            }

            public function getCourse(): object
            {
                return new class {
                    public function getTitle(): string
                    {
                        return '测试课程';
                    }
                };
            }

            public function getAuditTime(): \DateTime
            {
                return new \DateTime('-60 days');
            }
        };
    }

    /**
     * 创建已拒绝的审核
     */
    private function createRejectedAudit(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'rejected';
            }

            public function getCourse(): object
            {
                return new class {
                    public function getTitle(): string
                    {
                        return '测试课程2';
                    }
                };
            }

            public function getAuditTime(): \DateTime
            {
                return new \DateTime('-90 days');
            }
        };
    }

    /**
     * 创建待审核的审核
     */
    private function createPendingAudit(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'pending';
            }

            public function getCourse(): object
            {
                return new class {
                    public function getTitle(): string
                    {
                        return '待审核课程';
                    }
                };
            }

            /**
             * @phpstan-ignore return.unusedType
             */
            public function getAuditTime(): ?\DateTime
            {
                return null;
            }
        };
    }
}
