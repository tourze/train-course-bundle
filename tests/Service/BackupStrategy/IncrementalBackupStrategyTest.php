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
use Tourze\TrainCourseBundle\Service\BackupStrategy\BackupResultBuilder;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseDataSaver;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseSerializer;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseStatisticsCalculator;
use Tourze\TrainCourseBundle\Service\BackupStrategy\IncrementalBackupStrategy;
use Tourze\TrainCourseBundle\Service\BackupStrategy\MediaBackupService;

/**
 * @internal
 */
#[CoversClass(IncrementalBackupStrategy::class)]
final class IncrementalBackupStrategyTest extends TestCase
{
    private MockObject&CourseRepository $courseRepository;

    private MockObject&CourseSerializer $courseSerializer;

    private MockObject&MediaBackupService $mediaBackupService;

    private MockObject&CourseDataSaver $dataSaver;

    private MockObject&CourseStatisticsCalculator $statisticsCalculator;

    private MockObject&BackupResultBuilder $resultBuilder;

    private IncrementalBackupStrategy $strategy;

    private SymfonyStyle $io;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->courseSerializer = $this->createMock(CourseSerializer::class);
        $this->mediaBackupService = $this->createMock(MediaBackupService::class);
        $this->dataSaver = $this->createMock(CourseDataSaver::class);
        $this->statisticsCalculator = $this->createMock(CourseStatisticsCalculator::class);
        $this->resultBuilder = $this->createMock(BackupResultBuilder::class);

        $this->strategy = new IncrementalBackupStrategy(
            $this->courseRepository,
            $this->courseSerializer,
            $this->mediaBackupService,
            $this->dataSaver,
            $this->statisticsCalculator,
            $this->resultBuilder
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
        $this->assertInstanceOf(IncrementalBackupStrategy::class, $this->strategy);
    }

    public function testBackupWithNoCourses(): void
    {
        $courses = [];
        $emptyResult = [
            'course_count' => 0,
            'chapter_count' => 0,
            'lesson_count' => 0,
            'total_size' => 0,
        ];

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->resultBuilder
            ->expects($this->once())
            ->method('buildEmptyBackupResult')
            ->willReturn($emptyResult)
        ;

        // 不应该调用序列化、统计、保存等方法
        $this->courseSerializer->expects($this->never())->method('serializeCourses');
        $this->statisticsCalculator->expects($this->never())->method('calculateCourseStatistics');
        $this->dataSaver->expects($this->never())->method('saveIncrementalData');
        $this->mediaBackupService->expects($this->never())->method('backupMediaFilesIfRequired');

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertSame($emptyResult, $result);
    }

    public function testBackupWithUpdatedCourses(): void
    {
        $courses = [
            $this->createMockCourse(1, '更新的课程1'),
            $this->createMockCourse(2, '更新的课程2'),
        ];

        $courseData = [
            ['id' => 1, 'title' => '更新的课程1', 'chapters' => []],
            ['id' => 2, 'title' => '更新的课程2', 'chapters' => []],
        ];

        $stats = ['chapter_count' => 3, 'lesson_count' => 15];
        $dataFile = $this->tempDir . '/incremental_courses.json';
        file_put_contents($dataFile, json_encode(['courses' => $courseData]));
        $mediaSize = 512;

        $fileSize = filesize($dataFile);
        $this->assertNotFalse($fileSize);

        $expectedResult = [
            'course_count' => 2,
            'chapter_count' => 3,
            'lesson_count' => 15,
            'total_size' => $fileSize + $mediaSize,
        ];

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCourses')
            ->with($courses)
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
            ->method('saveIncrementalData')
            ->with($this->tempDir, $courseData)
            ->willReturn($dataFile)
        ;

        $this->mediaBackupService
            ->expects($this->once())
            ->method('backupMediaFilesIfRequired')
            ->with($this->tempDir, $courses, false, $this->io)
            ->willReturn($mediaSize)
        ;

        $this->resultBuilder
            ->expects($this->once())
            ->method('buildBackupResult')
            ->with(2, $stats, $dataFile, $mediaSize)
            ->willReturn($expectedResult)
        ;

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertSame($expectedResult, $result);
    }

    public function testBackupWithMediaIncluded(): void
    {
        $courses = [$this->createMockCourse(1, '课程1')];
        $courseData = [['id' => 1, 'title' => '课程1', 'chapters' => []]];
        $stats = ['chapter_count' => 1, 'lesson_count' => 5];
        $dataFile = $this->tempDir . '/incremental_courses.json';
        file_put_contents($dataFile, json_encode(['courses' => $courseData]));
        $mediaSize = 2048;

        $fileSize = filesize($dataFile);
        $this->assertNotFalse($fileSize);

        $expectedResult = [
            'course_count' => 1,
            'chapter_count' => 1,
            'lesson_count' => 5,
            'total_size' => $fileSize + $mediaSize,
        ];

        $this->courseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($courses)
        ;

        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCourses')
            ->with($courses)
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
            ->method('saveIncrementalData')
            ->with($this->tempDir, $courseData)
            ->willReturn($dataFile)
        ;

        $this->mediaBackupService
            ->expects($this->once())
            ->method('backupMediaFilesIfRequired')
            ->with($this->tempDir, $courses, true, $this->io)
            ->willReturn($mediaSize)
        ;

        $this->resultBuilder
            ->expects($this->once())
            ->method('buildBackupResult')
            ->with(1, $stats, $dataFile, $mediaSize)
            ->willReturn($expectedResult)
        ;

        $result = $this->strategy->backup($this->tempDir, true, $this->io);

        $this->assertSame($expectedResult, $result);
    }

    public function testBackupWithMediaExcluded(): void
    {
        $courses = [$this->createMockCourse(1, '课程1')];
        $courseData = [['id' => 1, 'title' => '课程1', 'chapters' => []]];
        $stats = ['chapter_count' => 1, 'lesson_count' => 5];
        $dataFile = $this->tempDir . '/incremental_courses.json';
        file_put_contents($dataFile, json_encode(['courses' => $courseData]));
        $mediaSize = 0;

        $expectedResult = [
            'course_count' => 1,
            'chapter_count' => 1,
            'lesson_count' => 5,
            'total_size' => filesize($dataFile),
        ];

        $this->courseRepository->method('findAll')->willReturn($courses);
        $this->courseSerializer->method('serializeCourses')->willReturn($courseData);
        $this->statisticsCalculator->method('calculateCourseStatistics')->willReturn($stats);
        $this->dataSaver->method('saveIncrementalData')->willReturn($dataFile);
        $this->mediaBackupService->method('backupMediaFilesIfRequired')->willReturn($mediaSize);
        $this->resultBuilder->method('buildBackupResult')->willReturn($expectedResult);

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertSame($expectedResult, $result);
    }

    public function testBackupUsesSerializeCoursesNotWithProgress(): void
    {
        $courses = [$this->createMockCourse(1, '课程1')];
        $courseData = [['id' => 1, 'title' => '课程1', 'chapters' => []]];
        $stats = ['chapter_count' => 1, 'lesson_count' => 5];
        $dataFile = $this->tempDir . '/incremental_courses.json';
        file_put_contents($dataFile, json_encode(['courses' => $courseData]));

        $this->courseRepository->method('findAll')->willReturn($courses);
        $this->statisticsCalculator->method('calculateCourseStatistics')->willReturn($stats);
        $this->dataSaver->method('saveIncrementalData')->willReturn($dataFile);
        $this->mediaBackupService->method('backupMediaFilesIfRequired')->willReturn(0);
        $this->resultBuilder->method('buildBackupResult')->willReturn([
            'course_count' => 1,
            'chapter_count' => 1,
            'lesson_count' => 5,
            'total_size' => 100,
        ]);

        // 验证使用的是 serializeCourses 而不是 serializeCoursesWithProgress
        $this->courseSerializer
            ->expects($this->once())
            ->method('serializeCourses')
            ->with($courses)
            ->willReturn($courseData)
        ;

        $this->strategy->backup($this->tempDir, false, $this->io);
    }

    public function testBackupReturnsCorrectStructure(): void
    {
        $courses = [];
        $emptyResult = [
            'course_count' => 0,
            'chapter_count' => 0,
            'lesson_count' => 0,
            'total_size' => 0,
        ];

        $this->courseRepository->method('findAll')->willReturn($courses);
        $this->resultBuilder->method('buildEmptyBackupResult')->willReturn($emptyResult);

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

    public function testBackupCallsResultBuilderForNonEmptyBackup(): void
    {
        $courses = [$this->createMockCourse(1, '课程1')];
        $courseData = [['id' => 1, 'title' => '课程1', 'chapters' => []]];
        $stats = ['chapter_count' => 1, 'lesson_count' => 5];
        $dataFile = $this->tempDir . '/incremental_courses.json';
        file_put_contents($dataFile, json_encode(['courses' => $courseData]));
        $mediaSize = 256;

        $this->courseRepository->method('findAll')->willReturn($courses);
        $this->courseSerializer->method('serializeCourses')->willReturn($courseData);
        $this->statisticsCalculator->method('calculateCourseStatistics')->willReturn($stats);
        $this->dataSaver->method('saveIncrementalData')->willReturn($dataFile);
        $this->mediaBackupService->method('backupMediaFilesIfRequired')->willReturn($mediaSize);

        $this->resultBuilder
            ->expects($this->once())
            ->method('buildBackupResult')
            ->with(1, $stats, $dataFile, $mediaSize)
        ;

        $this->resultBuilder
            ->expects($this->never())
            ->method('buildEmptyBackupResult')
        ;

        $this->strategy->backup($this->tempDir, false, $this->io);
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
