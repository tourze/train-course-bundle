<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseDataSaver;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseSerializer;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseStatisticsCalculator;
use Tourze\TrainCourseBundle\Service\BackupStrategy\FullBackupStrategy;
use Tourze\TrainCourseBundle\Service\BackupStrategy\MediaBackupService;

/**
 * @internal
 */
#[CoversClass(FullBackupStrategy::class)]
#[RunTestsInSeparateProcesses]
final class FullBackupStrategyTest extends AbstractIntegrationTestCase
{
    private FullBackupStrategy $strategy;
    private SymfonyStyle $io;
    private string $tempDir;

    protected function onSetUp(): void
    {
        // 获取真实的服务实例
        $this->strategy = self::getService(FullBackupStrategy::class);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);

        $this->tempDir = sys_get_temp_dir() . '/test_backup_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0o755, true);
        }
    }

    protected function onTearDown(): void
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

        parent::onTearDown();
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(FullBackupStrategy::class, $this->strategy);
    }

    public function testBackupWithNoCourses(): void
    {
        // 不创建任何课程数据，测试空数据库情况

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course_count', $result);
        $this->assertArrayHasKey('chapter_count', $result);
        $this->assertArrayHasKey('lesson_count', $result);
        $this->assertArrayHasKey('total_size', $result);

        $this->assertSame(0, $result['course_count']);
        $this->assertSame(0, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
        $this->assertSame(0, $result['total_size']);
    }

    public function testBackupWithMultipleCourses(): void
    {
        // 创建测试课程数据
        $courses = [];
        for ($i = 1; $i <= 3; $i++) {
            $course = new Course();
            $course->setTitle("课程{$i}");
            $course->setValid(true);
            $course->setSortNumber(100 - $i);
            $courses[] = $course;
        }

        // 保存到数据库
        $this->persistEntities($courses);

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertSame(3, $result['course_count']);
        $this->assertIsInt($result['chapter_count']);
        $this->assertIsInt($result['lesson_count']);
        $this->assertGreaterThan(0, $result['total_size']);
    }

    public function testBackupWithMediaIncluded(): void
    {
        // 创建测试课程数据
        $course = new Course();
        $course->setTitle('课程1');
        $course->setValid(true);
        $course->setSortNumber(100);

        $this->persistAndFlush($course);

        $result = $this->strategy->backup($this->tempDir, true, $this->io);

        $this->assertSame(1, $result['course_count']);
        $this->assertIsInt($result['chapter_count']);
        $this->assertIsInt($result['lesson_count']);
        $this->assertGreaterThan(0, $result['total_size']);
    }

    public function testBackupWithMediaExcluded(): void
    {
        // 创建测试课程数据
        $course = new Course();
        $course->setTitle('课程1');
        $course->setValid(true);
        $course->setSortNumber(100);

        $this->persistAndFlush($course);

        $result = $this->strategy->backup($this->tempDir, false, $this->io);

        $this->assertSame(1, $result['course_count']);
        $this->assertIsInt($result['chapter_count']);
        $this->assertIsInt($result['lesson_count']);
        $this->assertGreaterThan(0, $result['total_size']);
    }

    public function testBackupReturnsCorrectStructure(): void
    {
        // 测试空数据的返回结构
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
}
