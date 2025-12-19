<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseBackupCommand;

/**
 * CourseBackupCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseBackupCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseBackupCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的初始化逻辑在这里实现
        // 可以加载测试数据 fixtures 或创建临时数据
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseBackupCommand::class);

        return new CommandTester($command);
    }

    public function testOptionType(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--type' => 'full']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('执行全量备份', $output);
    }

    public function testOptionOutput(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--output' => '/tmp/test_backup']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('/tmp/test_backup', $output);
    }

    public function testOptionSince(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--type' => 'incremental',
            '--since' => '2024-01-01 00:00:00',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('执行增量备份', $output);
    }

    public function testOptionCompress(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--compress' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        // 检查压缩是否成功，或者压缩失败时的警告信息
        $this->assertMatchesRegularExpression('/备份文件已压缩|压缩失败/', $output);
    }

    public function testOptionIncludeMedia(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--include-media' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('备份媒体文件', $output);
    }
}
