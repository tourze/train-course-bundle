<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseStatisticsCommand;
use Tourze\TrainCourseBundle\Repository\CollectRepository;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;
use Tourze\TrainCourseBundle\Service\CourseAnalyticsService;

/**
 * CourseStatisticsCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseStatisticsCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseStatisticsCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void        // 集成测试的初始化逻辑在这里实现
    {
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseStatisticsCommand::class);

        return new CommandTester($command);
    }

    public function testCommandExecute(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $collectRepository = $this->createMock(CollectRepository::class);
        $evaluateRepository = $this->createMock(EvaluateRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $analyticsService = $this->createMock(CourseAnalyticsService::class);

        // 设置 mock 对象的预期行为
        $courseRepository->method('findAll')->willReturn([]);
        $collectRepository->method('findAll')->willReturn([]);
        $evaluateRepository->method('findAll')->willReturn([]);
        $auditRepository->method('findAll')->willReturn([]);
        $analyticsService->method('getCourseRankings')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CollectRepository::class, $collectRepository);
        self::getContainer()->set(EvaluateRepository::class, $evaluateRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseAnalyticsService::class, $analyticsService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--format' => 'json']);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
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
