<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CleanupTask;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CleanupSpecification\CourseCleanupSpecification;
use Tourze\TrainCourseBundle\Service\CleanupTask\CourseCleanupTask;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * @internal
 */
#[CoversClass(CourseCleanupTask::class)]
final class CourseCleanupTaskTest extends TestCase
{
    private CourseCleanupTask $task;

    private EntityManagerInterface $entityManager;

    private CourseRepository $courseRepository;

    private CourseConfigService $configService;

    private CourseCleanupSpecification $cleanupSpecification;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->configService = $this->createMock(CourseConfigService::class);
        $this->cleanupSpecification = $this->createMock(CourseCleanupSpecification::class);

        $this->task = new CourseCleanupTask(
            $this->entityManager,
            $this->courseRepository,
            $this->configService,
            $this->cleanupSpecification
        );

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseCleanupTask::class, $this->task);
    }

    public function testCleanupWithAutoCleanupDisabled(): void
    {
        $this->configService->method('get')
            ->with('course.auto_cleanup_expired', false)
            ->willReturn(false)
        ;

        $this->courseRepository->method('findExpiredCourses')
            ->willReturn([])
        ;

        $result = $this->task->cleanup($this->io, false);

        $this->assertSame(0, $result);
    }

    public function testCleanupWithNoExpiredCourses(): void
    {
        $this->configService->method('get')
            ->willReturn(true)
        ;

        $this->courseRepository->method('findExpiredCourses')
            ->willReturn([])
        ;

        $result = $this->task->cleanup($this->io, false);

        $this->assertSame(0, $result);
    }

    public function testCleanupWithExpiredCoursesInDryRun(): void
    {
        $this->configService->method('get')
            ->willReturn(true)
        ;

        $courses = [
            $this->createExpiredCourse(),
        ];

        $this->courseRepository->method('findExpiredCourses')
            ->willReturn($courses)
        ;

        $this->cleanupSpecification->method('shouldCleanupCourse')
            ->willReturn(true)
        ;

        $this->entityManager->expects($this->never())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, true);

        $this->assertSame(1, $result);
    }

    public function testCleanupWithExpiredCoursesNotInDryRun(): void
    {
        $this->configService->method('get')
            ->willReturn(true)
        ;

        $courses = [
            $this->createExpiredCourse(),
        ];

        $this->courseRepository->method('findExpiredCourses')
            ->willReturn($courses)
        ;

        $this->cleanupSpecification->method('shouldCleanupCourse')
            ->willReturn(true)
        ;

        $this->entityManager->expects($this->once())
            ->method('remove')
        ;

        $this->entityManager->expects($this->once())
            ->method('flush')
        ;

        $result = $this->task->cleanup($this->io, false);

        $this->assertSame(1, $result);
    }

    public function testCleanupSkipsCoursesNotMeetingSpecification(): void
    {
        $this->configService->method('get')
            ->willReturn(true)
        ;

        $courses = [
            $this->createExpiredCourse(),
            $this->createExpiredCourse(),
        ];

        $this->courseRepository->method('findExpiredCourses')
            ->willReturn($courses)
        ;

        $this->cleanupSpecification->method('shouldCleanupCourse')
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $this->entityManager->expects($this->once())
            ->method('remove')
        ;

        $result = $this->task->cleanup($this->io, false);

        $this->assertSame(1, $result);
    }

    public function testCleanupDoesNotFlushWhenNoCoursesDeleted(): void
    {
        $this->configService->method('get')
            ->willReturn(true)
        ;

        $courses = [
            $this->createExpiredCourse(),
        ];

        $this->courseRepository->method('findExpiredCourses')
            ->willReturn($courses)
        ;

        $this->cleanupSpecification->method('shouldCleanupCourse')
            ->willReturn(false)
        ;

        $this->entityManager->expects($this->never())
            ->method('flush')
        ;

        $result = $this->task->cleanup($this->io, false);

        $this->assertSame(0, $result);
    }

    /**
     * 创建过期的课程
     */
    private function createExpiredCourse(): object
    {
        return new class {
            public function getId(): int
            {
                return 1;
            }

            public function getTitle(): string
            {
                return '过期课程';
            }
        };
    }
}
