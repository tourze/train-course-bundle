<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseCleanupCommand;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * CourseCleanupCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseCleanupCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseCleanupCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void        // 集成测试的初始化逻辑在这里实现
    {
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseCleanupCommand::class);

        return new CommandTester($command);
    }

    public function testCommandExecute(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $versionRepository = $this->createMock(CourseVersionRepository::class);
        $auditRepository = $this->createMock(CourseAuditRepository::class);
        $configService = $this->createMock(CourseConfigService::class);

        // 设置 mock 对象的预期行为
        $courseRepository->method('findAll')->willReturn([]);
        $versionRepository->method('findAll')->willReturn([]);
        $auditRepository->method('findAll')->willReturn([]);
        $configService->method('get')->willReturn(null);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);
        self::getContainer()->set(CourseVersionRepository::class, $versionRepository);
        self::getContainer()->set(CourseAuditRepository::class, $auditRepository);
        self::getContainer()->set(CourseConfigService::class, $configService);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--clear-cache' => true]);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
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
