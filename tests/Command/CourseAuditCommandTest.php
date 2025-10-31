<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseAuditCommand;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

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
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseAuditCommand::class);

        return new CommandTester($command);
    }

    public function testCommandExecute(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $configService = $this->createMock(CourseConfigService::class);

        // 设置 mock 对象的预期行为
        $courseRepository->method('find')->willReturn(null);
        $auditRepository->method('find')->willReturn(null);
        $configService->method('get')->willReturn(72);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseConfigService::class, $configService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testOptionAutoApprove(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $configService = $this->createMock(CourseConfigService::class);

        // 设置 mock 对象的预期行为
        $auditRepository->method('findPendingAudits')->willReturn([]);
        $configService->method('get')->willReturn(true);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseConfigService::class, $configService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--auto-approve' => true, '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('自动审核课程', $output);
    }

    public function testOptionCheckTimeout(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $configService = $this->createMock(CourseConfigService::class);

        // 设置 mock 对象的预期行为
        $auditRepository->method('findTimeoutAudits')->willReturn([]);
        $configService->method('get')->willReturn(72);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseConfigService::class, $configService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--check-timeout' => true, '--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('检查审核超时', $output);
    }

    public function testOptionCourseId(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $configService = $this->createMock(CourseConfigService::class);

        // 设置 mock 对象的预期行为 - 课程不存在的情况
        $courseRepository->method('find')->with('123')->willReturn(null);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseConfigService::class, $configService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--course-id' => '123', '--dry-run' => true]);

        // 验证命令执行失败（课程不存在）
        $this->assertSame(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('课程 ID 123 不存在', $output);
    }

    public function testOptionDryRun(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $configService = $this->createMock(CourseConfigService::class);

        // 设置 mock 对象的预期行为
        $auditRepository->method('findPendingAudits')->willReturn([]);
        $auditRepository->method('findTimeoutAudits')->willReturn([]);
        $configService->method('get')->willReturn(72);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseConfigService::class, $configService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--dry-run' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('运行在试运行模式', $output);
    }
}
