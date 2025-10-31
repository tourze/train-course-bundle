<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupUtil;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupDirectoryManager;

/**
 * @internal
 */
#[CoversClass(BackupDirectoryManager::class)]
final class BackupDirectoryManagerTest extends TestCase
{
    private BackupDirectoryManager $manager;

    protected function setUp(): void
    {
        $this->manager = new BackupDirectoryManager();
    }

    protected function tearDown(): void
    {
        // 清理可能创建的测试目录
        $pattern = sys_get_temp_dir() . '/test_backup_*';
        $dirs = glob($pattern);
        if (false !== $dirs) {
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $this->manager->removeDirectory($dir);
                }
            }
        }
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BackupDirectoryManager::class, $this->manager);
    }

    public function testCreateBackupDirectoryCreatesNewDirectory(): void
    {
        $basePath = sys_get_temp_dir() . '/test_backup';

        $result = $this->manager->createBackupDirectory($basePath);

        $this->assertIsString($result);
        $this->assertStringContainsString('course_backup_', $result);
        $this->assertStringContainsString(date('Y-m-d'), $result);
    }

    public function testCreateBackupDirectoryWithTrailingSlash(): void
    {
        $basePath = sys_get_temp_dir() . '/test_backup/';

        $result = $this->manager->createBackupDirectory($basePath);

        $this->assertIsString($result);
        $this->assertStringContainsString('course_backup_', $result);
    }

    public function testCreateBackupDirectoryCreatesMultipleUniqueDirectories(): void
    {
        $basePath = sys_get_temp_dir() . '/test_backup';

        $result1 = $this->manager->createBackupDirectory($basePath);
        sleep(1); // 确保时间戳不同
        $result2 = $this->manager->createBackupDirectory($basePath);

        $this->assertNotSame($result1, $result2);
    }

    public function testRemoveDirectoryHandlesNonExistentDirectory(): void
    {
        $nonExistentDir = sys_get_temp_dir() . '/non_existent_dir_12345_' . uniqid();

        // 确保目录不存在
        $exists = is_dir($nonExistentDir);
        $this->assertFalse($exists);
        $this->assertDirectoryDoesNotExist($nonExistentDir);

        // 不应该抛出异常
        $this->manager->removeDirectory($nonExistentDir);

        // 验证仍然不存在，且没有抛出异常
        $this->assertDirectoryDoesNotExist($nonExistentDir);
    }

    public function testRemoveDirectoryHandlesEmptyDirectory(): void
    {
        $tempDir = sys_get_temp_dir() . '/test_empty_dir_' . uniqid();
        mkdir($tempDir, 0o755, true);
        $this->assertDirectoryExists($tempDir);

        $this->manager->removeDirectory($tempDir);

        $this->assertDirectoryDoesNotExist($tempDir);
    }

    public function testRemoveDirectoryHandlesDirectoryWithFiles(): void
    {
        $tempDir = sys_get_temp_dir() . '/test_dir_with_files_' . uniqid();
        mkdir($tempDir, 0o755, true);
        file_put_contents($tempDir . '/test.txt', 'test content');
        $this->assertDirectoryExists($tempDir);
        $this->assertFileExists($tempDir . '/test.txt');

        $this->manager->removeDirectory($tempDir);

        $this->assertDirectoryDoesNotExist($tempDir);
    }

    public function testRemoveDirectoryHandlesNestedDirectories(): void
    {
        $tempDir = sys_get_temp_dir() . '/test_nested_' . uniqid();
        $nestedDir = $tempDir . '/level1/level2';
        mkdir($nestedDir, 0o755, true);
        file_put_contents($nestedDir . '/test.txt', 'test content');
        file_put_contents($tempDir . '/level1/test.txt', 'test content');
        $this->assertDirectoryExists($tempDir);
        $this->assertDirectoryExists($nestedDir);

        $this->manager->removeDirectory($tempDir);

        $this->assertDirectoryDoesNotExist($tempDir);
    }
}
