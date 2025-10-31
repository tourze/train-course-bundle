<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CleanupTask;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;
use Tourze\TrainCourseBundle\Service\CleanupTask\VersionCleanupTask;

/**
 * @internal
 */
#[CoversClass(VersionCleanupTask::class)]
final class VersionCleanupTaskTest extends TestCase
{
    private VersionCleanupTask $task;

    private EntityManagerInterface $entityManager;

    private CourseVersionRepository $versionRepository;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->versionRepository = $this->createMock(CourseVersionRepository::class);
        $this->task = new VersionCleanupTask($this->entityManager, $this->versionRepository);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(VersionCleanupTask::class, $this->task);
    }

    public function testCleanupWithNoOldVersions(): void
    {
        $this->versionRepository->method('findOldVersions')
            ->willReturn([])
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(0, $result);
    }

    public function testCleanupWithOldVersionsInDryRun(): void
    {
        $versions = [
            $this->createDraftVersion(),
            $this->createArchivedVersion(),
        ];

        $this->versionRepository->method('findOldVersions')
            ->willReturn($versions)
        ;

        $this->entityManager->expects($this->never())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, 30, true);

        $this->assertSame(2, $result);
    }

    public function testCleanupWithOldVersionsNotInDryRun(): void
    {
        $versions = [
            $this->createDraftVersion(),
            $this->createArchivedVersion(),
        ];

        $this->versionRepository->method('findOldVersions')
            ->willReturn($versions)
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

    public function testCleanupSkipsCurrentVersions(): void
    {
        $versions = [
            $this->createCurrentVersion(),
            $this->createDraftVersion(),
        ];

        $this->versionRepository->method('findOldVersions')
            ->willReturn($versions)
        ;

        $this->entityManager->expects($this->once())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(1, $result);
    }

    public function testCleanupSkipsPublishedVersions(): void
    {
        $versions = [
            $this->createPublishedVersion(),
            $this->createDraftVersion(),
        ];

        $this->versionRepository->method('findOldVersions')
            ->willReturn($versions)
        ;

        $this->entityManager->expects($this->once())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(1, $result);
    }

    public function testCleanupDoesNotFlushWhenNoVersionsDeleted(): void
    {
        $versions = [
            $this->createCurrentVersion(),
            $this->createPublishedVersion(),
        ];

        $this->versionRepository->method('findOldVersions')
            ->willReturn($versions)
        ;

        $this->entityManager->expects($this->never())
            ->method('flush')
        ;

        $result = $this->task->cleanup($this->io, 30, false);

        $this->assertSame(0, $result);
    }

    /**
     * 创建草稿版本
     */
    private function createDraftVersion(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'draft';
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

            public function getVersion(): string
            {
                return '1.0.0';
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-60 days');
            }
        };
    }

    /**
     * 创建已归档版本
     */
    private function createArchivedVersion(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'archived';
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

            public function getVersion(): string
            {
                return '2.0.0';
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-90 days');
            }
        };
    }

    /**
     * 创建当前版本
     */
    private function createCurrentVersion(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'current';
            }

            public function getCourse(): object
            {
                return new class {
                    public function getTitle(): string
                    {
                        return '当前课程';
                    }
                };
            }

            public function getVersion(): string
            {
                return '3.0.0';
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-30 days');
            }
        };
    }

    /**
     * 创建已发布版本
     */
    private function createPublishedVersion(): object
    {
        return new class {
            public function getStatus(): string
            {
                return 'published';
            }

            public function getCourse(): object
            {
                return new class {
                    public function getTitle(): string
                    {
                        return '已发布课程';
                    }
                };
            }

            public function getVersion(): string
            {
                return '4.0.0';
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-120 days');
            }
        };
    }
}
