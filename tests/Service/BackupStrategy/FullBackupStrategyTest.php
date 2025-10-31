<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseDataSaver;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseSerializer;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseStatisticsCalculator;
use Tourze\TrainCourseBundle\Service\BackupStrategy\FullBackupStrategy;
use Tourze\TrainCourseBundle\Service\BackupStrategy\MediaBackupService;

/**
 * @internal
 */
#[CoversClass(FullBackupStrategy::class)]
final class FullBackupStrategyTest extends TestCase
{
    private MockObject&CourseRepository $courseRepository;

    private MockObject&CourseSerializer $courseSerializer;

    private MockObject&MediaBackupService $mediaBackupService;

    private MockObject&CourseDataSaver $dataSaver;

    private MockObject&CourseStatisticsCalculator $statisticsCalculator;

    private FullBackupStrategy $strategy;

    private SymfonyStyle $io;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->courseSerializer = $this->createMock(CourseSerializer::class);
        $this->mediaBackupService = $this->createMock(MediaBackupService::class);
        $this->dataSaver = $this->createMock(CourseDataSaver::class);
        $this->statisticsCalculator = $this->createMock(CourseStatisticsCalculator::class);

        $this->strategy = new FullBackupStrategy(
            $this->courseRepository,
            $this->courseSerializer,
            $this->mediaBackupService,
            $this->dataSaver,
            $this->statisticsCalculator
        );

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);

        $this->tempDir = sys_get_temp_dir() . '/test_backup_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0o755, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if (false !== $files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(FullBackupStrategy::class, $this->strategy);
    }

    public function testBackupWithNoCourses(): void
    {
        $courses = [];
        $courseData = [];
        $stats = ['chapter_count' => 0, 'lesson_count' => 0];
        $dataFile = $this->tempDir . '/courses.json';
        file_put_contents($dataFile, '[]');
        $mediaSize = 0;

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCoursesWithProgress')
            ->with($courses, $this->io)
            ->willReturn($courseData)
        ;

        $this->statisticsCalculator
            ->expects($this->once())
            ->method('calculateCourseStatistics')
            ->with($courseData)
            ->willReturn($stats)
        ;

        $this->dataSaver
            ->expects($this->once())
            ->method('saveCourseData')
            ->with($this->tempDir, $courseData, 'courses.json')
            ->willReturn($dataFile)
        ;

        $this->mediaBackupService
            ->expects($this->once())
            ->method('backupMediaFilesIfRequired')
            ->with($this->tempDir, $courses, false, $this->io)
            ->willReturn($mediaSize)
        ;

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course_count', $result);
        $this->assertArrayHasKey('chapter_count', $result);
        $this->assertArrayHasKey('lesson_count', $result);
        $this->assertArrayHasKey('total_size', $result);

        $this->assertSame(0, $result['course_count']);
        $this->assertSame(0, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
    }

    public function testBackupWithMultipleCourses(): void
    {
        $courses = [
            $this->createMockCourse(1, '课程1'),
            $this->createMockCourse(2, '课程2'),
            $this->createMockCourse(3, '课程3'),
        ];

        $courseData = [
            ['id' => 1, 'title' => '课程1', 'chapters' => []],
            ['id' => 2, 'title' => '课程2', 'chapters' => []],
            ['id' => 3, 'title' => '课程3', 'chapters' => []],
        ];

        $stats = ['chapter_count' => 5, 'lesson_count' => 25];
        $dataFile = $this->tempDir . '/courses.json';
        file_put_contents($dataFile, json_encode($courseData));
        $mediaSize = 1024;

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCoursesWithProgress')
            ->with($courses, $this->io)
            ->willReturn($courseData)
        ;

        $this->statisticsCalculator
            ->expects($this->once())
            ->method('calculateCourseStatistics')
            ->with($courseData)
            ->willReturn($stats)
        ;

        $this->dataSaver
            ->expects($this->once())
            ->method('saveCourseData')
            ->with($this->tempDir, $courseData, 'courses.json')
            ->willReturn($dataFile)
        ;

        $this->mediaBackupService
            ->expects($this->once())
            ->method('backupMediaFilesIfRequired')
            ->with($this->tempDir, $courses, false, $this->io)
            ->willReturn($mediaSize)
        ;

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertSame(3, $result['course_count']);
        $this->assertSame(5, $result['chapter_count']);
        $this->assertSame(25, $result['lesson_count']);
        $this->assertGreaterThan(0, $result['total_size']);
    }

    public function testBackupWithMediaIncluded(): void
    {
        $courses = [$this->createMockCourse(1, '课程1')];
        $courseData = [['id' => 1, 'title' => '课程1', 'chapters' => []]];
        $stats = ['chapter_count' => 2, 'lesson_count' => 10];
        $dataFile = $this->tempDir . '/courses.json';
        file_put_contents($dataFile, json_encode($courseData));
        $mediaSize = 5120; // 5KB

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCoursesWithProgress')
            ->with($courses, $this->io)
            ->willReturn($courseData)
        ;

        $this->statisticsCalculator
            ->expects($this->once())
            ->method('calculateCourseStatistics')
            ->with($courseData)
            ->willReturn($stats)
        ;

        $this->dataSaver
            ->expects($this->once())
            ->method('saveCourseData')
            ->with($this->tempDir, $courseData, 'courses.json')
            ->willReturn($dataFile)
        ;

        $this->mediaBackupService
            ->expects($this->once())
            ->method('backupMediaFilesIfRequired')
            ->with($this->tempDir, $courses, true, $this->io)
            ->willReturn($mediaSize)
        ;

        $result = $this->strategy->backup($this->tempDir, true, $this->io);

        $this->assertSame(1, $result['course_count']);
        $this->assertSame(2, $result['chapter_count']);
        $this->assertSame(10, $result['lesson_count']);

        $fileSize = filesize($dataFile);
        $this->assertNotFalse($fileSize);
        $expectedTotalSize = $fileSize + $mediaSize;
        $this->assertSame($expectedTotalSize, $result['total_size']);
    }

    public function testBackupWithMediaExcluded(): void
    {
        $courses = [$this->createMockCourse(1, '课程1')];
        $courseData = [['id' => 1, 'title' => '课程1', 'chapters' => []]];
        $stats = ['chapter_count' => 1, 'lesson_count' => 5];
        $dataFile = $this->tempDir . '/courses.json';
        file_put_contents($dataFile, json_encode($courseData));
        $mediaSize = 0;

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCoursesWithProgress')
            ->with($courses, $this->io)
            ->willReturn($courseData)
        ;

        $this->statisticsCalculator
            ->expects($this->once())
            ->method('calculateCourseStatistics')
            ->with($courseData)
            ->willReturn($stats)
        ;

        $this->dataSaver
            ->expects($this->once())
            ->method('saveCourseData')
            ->with($this->tempDir, $courseData, 'courses.json')
            ->willReturn($dataFile)
        ;

        $this->mediaBackupService
            ->expects($this->once())
            ->method('backupMediaFilesIfRequired')
            ->with($this->tempDir, $courses, false, $this->io)
            ->willReturn($mediaSize)
        ;

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $fileSize = filesize($dataFile);
        $this->assertNotFalse($fileSize);
        $this->assertSame($fileSize, $result['total_size']);
    }

    public function testBackupReturnsCorrectStructure(): void
    {
        $courses = [];
        $courseData = [];
        $stats = ['chapter_count' => 0, 'lesson_count' => 0];
        $dataFile = $this->tempDir . '/courses.json';
        file_put_contents($dataFile, '[]');
        $mediaSize = 0;

        $this->courseRepository->method('findAll')->willReturn($courses);
        $this->courseSerializer->method('serializeCoursesWithProgress')->willReturn($courseData);
        $this->statisticsCalculator->method('calculateCourseStatistics')->willReturn($stats);
        $this->dataSaver->method('saveCourseData')->willReturn($dataFile);
        $this->mediaBackupService->method('backupMediaFilesIfRequired')->willReturn($mediaSize);

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('course_count', $result);
        $this->assertArrayHasKey('chapter_count', $result);
        $this->assertArrayHasKey('lesson_count', $result);
        $this->assertArrayHasKey('total_size', $result);

        $this->assertIsInt($result['course_count']);
        $this->assertIsInt($result['chapter_count']);
        $this->assertIsInt($result['lesson_count']);
        $this->assertIsInt($result['total_size']);
    }

    /**
     * 创建Mock课程对象
     */
    private function createMockCourse(int $id, string $title): object
    {
        return new class($id, $title) {
            public function __construct(private readonly int $id, private readonly string $title)
            {
            }

            public function getId(): int
            {
                return $this->id;
            }

            public function getTitle(): string
            {
                return $this->title;
            }
        };
    }
}
