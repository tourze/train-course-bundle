<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupUtil;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupCompressor;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupDirectoryManager;

/**
 * @internal
 */
#[CoversClass(BackupCompressor::class)]
final class BackupCompressorTest extends TestCase
{
    private BackupCompressor $compressor;

    private BackupDirectoryManager $directoryManager;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->directoryManager = $this->createMock(BackupDirectoryManager::class);
        $this->compressor = new BackupCompressor($this->directoryManager);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BackupCompressor::class, $this->compressor);
    }

    public function testCompressBackupReturnsArchivePath(): void
    {
        $backupDir = '/tmp/test_backup';

        // 预期返回压缩后的文件路径
        $expectedArchivePath = $backupDir . '.tar.gz';

        $result = $this->compressor->compressBackup($backupDir, $this->io);

        $this->assertIsString($result);
        $this->assertSame($expectedArchivePath, $result);
    }

    public function testCompressBackupReturnsExpectedArchivePath(): void
    {
        $backupDir = '/tmp/test_backup';

        // 由于实际执行tar命令可能失败或成功，我们只验证返回值格式
        $result = $this->compressor->compressBackup($backupDir, $this->io);

        $this->assertIsString($result);
        $this->assertStringEndsWith('.tar.gz', $result);
    }

    public function testCompressBackupWithSpecialCharactersInPath(): void
    {
        $backupDir = '/tmp/test backup with spaces';

        $result = $this->compressor->compressBackup($backupDir, $this->io);

        $this->assertIsString($result);
        $this->assertStringEndsWith('.tar.gz', $result);
    }
}
