<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseCleanupCommand;

/**
 * CourseCleanupCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseCleanupCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseCleanupCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的初始化逻辑在这里实现
        // 可以加载测试数据 fixtures 或创建临时数据
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseCleanupCommand::class);

        return new CommandTester($command);
    }

    public function testOptionClearCache(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--clear-cache' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionCleanupVersions(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--cleanup-versions' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionCleanupAudits(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--cleanup-audits' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionCleanupExpired(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--cleanup-expired' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionDays(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--days' => '60']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionDryRun(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--dry-run' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }
}
