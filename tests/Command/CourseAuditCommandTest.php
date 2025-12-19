<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseAuditCommand;

/**
 * CourseAuditCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseAuditCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseAuditCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的初始化逻辑在这里实现
        // 可以加载测试数据 fixtures 或创建临时数据
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseAuditCommand::class);

        return new CommandTester($command);
    }

    public function testOptionAutoApprove(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--auto-approve' => true, '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('自动审核课程', $output);
    }

    public function testOptionCheckTimeout(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--check-timeout' => true, '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('检查审核超时', $output);
    }

    public function testOptionCourseId(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--course-id' => '123', '--dry-run' => true]);

        // 验证命令执行失败（课程不存在）
        $this->assertSame(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('课程 ID 123 不存在', $output);
    }

    public function testOptionDryRun(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('运行在试运行模式', $output);
    }
}
