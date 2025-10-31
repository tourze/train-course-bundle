<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupUtil;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\BackupUtil\BackupReportGenerator;

/**
 * @internal
 */
#[CoversClass(BackupReportGenerator::class)]
final class BackupReportGeneratorTest extends TestCase
{
    private BackupReportGenerator $generator;

    private SymfonyStyle $io;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->generator = new BackupReportGenerator();

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);

        $this->tempDir = sys_get_temp_dir() . '/backup_report_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = array_diff(scandir($this->tempDir), ['.', '..']);
            foreach ($files as $file) {
                unlink($this->tempDir . '/' . $file);
            }
            rmdir($this->tempDir);
        }
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BackupReportGenerator::class, $this->generator);
    }

    public function testGenerateBackupReportCreatesReportFile(): void
    {
        $backupInfo = [
            'course_count' => 10,
            'chapter_count' => 50,
            'lesson_count' => 200,
            'total_size' => 1024 * 1024 * 100, // 100MB
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $this->assertFileExists($reportFile);
    }

    public function testGenerateBackupReportContainsCorrectInformation(): void
    {
        $backupInfo = [
            'course_count' => 5,
            'chapter_count' => 20,
            'lesson_count' => 100,
            'total_size' => 1024 * 1024 * 50, // 50MB
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $content = file_get_contents($reportFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('课程备份报告', $content);
        $this->assertStringContainsString('课程数量: 5', $content);
        $this->assertStringContainsString('章节数量: 20', $content);
        $this->assertStringContainsString('课时数量: 100', $content);
        $this->assertStringContainsString('MB', $content);
    }

    public function testGenerateBackupReportHandlesEmptyData(): void
    {
        $backupInfo = [
            'course_count' => 0,
            'chapter_count' => 0,
            'lesson_count' => 0,
            'total_size' => 0,
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $content = file_get_contents($reportFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('课程数量: 0', $content);
        $this->assertStringContainsString('0 B', $content);
    }

    public function testGenerateBackupReportHandlesInvalidDataTypes(): void
    {
        $backupInfo = [
            'course_count' => 'invalid',
            'chapter_count' => null,
            'lesson_count' => [],
            'total_size' => 'not_a_number',
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $content = file_get_contents($reportFile);

        $this->assertIsString($content);
        // 应该回退到默认值0
        $this->assertStringContainsString('课程数量: 0', $content);
        $this->assertStringContainsString('章节数量: 0', $content);
    }

    public function testGenerateBackupReportFormatsLargeSizes(): void
    {
        $backupInfo = [
            'course_count' => 100,
            'chapter_count' => 500,
            'lesson_count' => 2000,
            'total_size' => 1024 * 1024 * 1024 * 5, // 5GB
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $content = file_get_contents($reportFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('GB', $content);
    }

    public function testGenerateBackupReportFormatsSmallSizes(): void
    {
        $backupInfo = [
            'course_count' => 1,
            'chapter_count' => 5,
            'lesson_count' => 10,
            'total_size' => 500, // 500 bytes
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $content = file_get_contents($reportFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('B', $content);
    }

    public function testGenerateBackupReportIncludesTimestamp(): void
    {
        $backupInfo = [
            'course_count' => 1,
            'chapter_count' => 1,
            'lesson_count' => 1,
            'total_size' => 1024,
        ];

        $this->generator->generateBackupReport($this->tempDir, $backupInfo, $this->io);

        $reportFile = $this->tempDir . '/backup_report.txt';
        $content = file_get_contents($reportFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('备份时间:', $content);
        $this->assertStringContainsString(date('Y-m-d'), $content);
    }
}
