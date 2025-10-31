<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\BackupStrategy\BackupResultBuilder;

/**
 * @internal
 */
#[CoversClass(BackupResultBuilder::class)]
final class BackupResultBuilderTest extends TestCase
{
    private BackupResultBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new BackupResultBuilder();
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BackupResultBuilder::class, $this->builder);
    }

    public function testBuildEmptyBackupResult(): void
    {
        $result = $this->builder->buildEmptyBackupResult();

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

    public function testBuildBackupResultWithValidData(): void
    {
        $courseCount = 5;
        $stats = [
            'chapter_count' => 10,
            'lesson_count' => 50,
        ];
        $tempFile = tempnam(sys_get_temp_dir(), 'test_backup_');
        $this->assertNotFalse($tempFile);
        file_put_contents($tempFile, 'test content');
        $mediaSize = 1024;

        $result = $this->builder->buildBackupResult($courseCount, $stats, $tempFile, $mediaSize);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course_count', $result);
        $this->assertArrayHasKey('chapter_count', $result);
        $this->assertArrayHasKey('lesson_count', $result);
        $this->assertArrayHasKey('total_size', $result);

        $this->assertSame(5, $result['course_count']);
        $this->assertSame(10, $result['chapter_count']);
        $this->assertSame(50, $result['lesson_count']);

        $expectedFileSize = strlen('test content');
        $this->assertSame($expectedFileSize + $mediaSize, $result['total_size']);

        unlink($tempFile);
    }

    public function testBuildBackupResultWithNonExistentFile(): void
    {
        $courseCount = 3;
        $stats = [
            'chapter_count' => 6,
            'lesson_count' => 30,
        ];
        $nonExistentFile = '/path/to/non/existent/file.json';
        $mediaSize = 512;

        $result = $this->builder->buildBackupResult($courseCount, $stats, $nonExistentFile, $mediaSize);

        $this->assertIsArray($result);
        $this->assertSame(3, $result['course_count']);
        $this->assertSame(6, $result['chapter_count']);
        $this->assertSame(30, $result['lesson_count']);
        $this->assertSame(512, $result['total_size']); // filesize returns false, so 0 + mediaSize
    }

    public function testBuildBackupResultWithZeroMediaSize(): void
    {
        $courseCount = 2;
        $stats = [
            'chapter_count' => 4,
            'lesson_count' => 20,
        ];
        $tempFile = tempnam(sys_get_temp_dir(), 'test_backup_');
        $this->assertNotFalse($tempFile);
        file_put_contents($tempFile, 'content');
        $mediaSize = 0;

        $result = $this->builder->buildBackupResult($courseCount, $stats, $tempFile, $mediaSize);

        $expectedFileSize = strlen('content');
        $this->assertSame($expectedFileSize, $result['total_size']);

        unlink($tempFile);
    }

    public function testBuildBackupResultWithEmptyFile(): void
    {
        $courseCount = 1;
        $stats = [
            'chapter_count' => 2,
            'lesson_count' => 10,
        ];
        $tempFile = tempnam(sys_get_temp_dir(), 'test_backup_');
        $this->assertNotFalse($tempFile);
        file_put_contents($tempFile, '');
        $mediaSize = 256;

        $result = $this->builder->buildBackupResult($courseCount, $stats, $tempFile, $mediaSize);

        $this->assertSame(0 + 256, $result['total_size']);

        unlink($tempFile);
    }
}
