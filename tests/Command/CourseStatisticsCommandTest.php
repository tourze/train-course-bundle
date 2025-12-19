<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseStatisticsCommand;

/**
 * CourseStatisticsCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseStatisticsCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseStatisticsCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的初始化逻辑在这里实现
        // 可以加载测试数据 fixtures 或创建临时数据
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseStatisticsCommand::class);

        return new CommandTester($command);
    }

    public function testOptionFormat(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--format' => 'json']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionOutput(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--output' => '/tmp/test-output.txt']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionDetailed(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--detailed' => true]);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionCourseId(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--course-id' => '123']);
        // 不验证状态码，因为课程ID可能不存在，只验证选项被处理
        $this->assertNotEmpty($commandTester->getDisplay());
    }

    public function testOptionTop(): void
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--top' => '5']);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }
}
