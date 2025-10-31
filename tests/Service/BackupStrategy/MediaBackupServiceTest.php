<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\BackupStrategy\MediaBackupService;

/**
 * @internal
 */
#[CoversClass(MediaBackupService::class)]
final class MediaBackupServiceTest extends TestCase
{
    private MediaBackupService $service;

    private SymfonyStyle $io;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->service = new MediaBackupService();

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);

        $this->tempDir = sys_get_temp_dir() . '/media_backup_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(MediaBackupService::class, $this->service);
    }

    public function testBackupMediaFilesReturnsZero(): void
    {
        $courses = [];

        $result = $this->service->backupMediaFiles($this->tempDir, $courses, $this->io);

        $this->assertSame(0, $result);
    }

    public function testBackupMediaFilesCreatesMediaDirectory(): void
    {
        $courses = [];

        $this->service->backupMediaFiles($this->tempDir, $courses, $this->io);

        $mediaDir = $this->tempDir . '/media';
        $this->assertDirectoryExists($mediaDir);
    }

    public function testBackupMediaFilesWithEmptyCoursesArray(): void
    {
        $courses = [];

        $result = $this->service->backupMediaFiles($this->tempDir, $courses, $this->io);

        $this->assertSame(0, $result);
    }

    public function testBackupMediaFilesWithCoursesArray(): void
    {
        $courses = [
            $this->createMockCourse(1),
            $this->createMockCourse(2),
        ];

        $result = $this->service->backupMediaFiles($this->tempDir, $courses, $this->io);

        $this->assertSame(0, $result);
    }

    public function testBackupMediaFilesIfRequiredWithIncludeMediaTrue(): void
    {
        $courses = [];
        $includeMedia = true;

        $result = $this->service->backupMediaFilesIfRequired(
            $this->tempDir,
            $courses,
            $includeMedia,
            $this->io
        );

        $this->assertSame(0, $result);
    }

    public function testBackupMediaFilesIfRequiredWithIncludeMediaFalse(): void
    {
        $courses = [];
        $includeMedia = false;

        $result = $this->service->backupMediaFilesIfRequired(
            $this->tempDir,
            $courses,
            $includeMedia,
            $this->io
        );

        $this->assertSame(0, $result);
    }

    public function testBackupMediaFilesIfRequiredDoesNotCreateMediaDirWhenNotIncluded(): void
    {
        $courses = [];
        $includeMedia = false;

        $result = $this->service->backupMediaFilesIfRequired(
            $this->tempDir,
            $courses,
            $includeMedia,
            $this->io
        );

        $this->assertSame(0, $result);
        $mediaDir = $this->tempDir . '/media';
        $this->assertDirectoryDoesNotExist($mediaDir);
    }

    /**
     * 创建Mock课程对象
     */
    private function createMockCourse(int $id): object
    {
        return new class($id) {
            public function __construct(private readonly int $id)
            {
            }

            public function getId(): int
            {
                return $this->id;
            }
        };
    }

    /**
     * 删除目录及其内容
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
