<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainCourseBundle\Command\CourseBackupCommand;
use Tourze\TrainCourseBundle\Repository\CourseRepository;

/**
 * CourseBackupCommand 集成测试
 *
 * @internal
 */
#[CoversClass(CourseBackupCommand::class)]
#[RunTestsInSeparateProcesses]
final class CourseBackupCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void        // 集成测试的初始化逻辑在这里实现
    {
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CourseBackupCommand::class);

        return new CommandTester($command);
    }

    public function testCommandExecute(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);

        // 设置 mock 对象的预期行为
        $courseRepository->method('findAll')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--type' => 'full']);

        // 验证命令执行成功
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testOptionType(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $courseRepository->method('findAll')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--type' => 'full']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('执行全量备份', $output);
    }

    public function testOptionOutput(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $courseRepository->method('findAll')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--output' => '/tmp/test_backup']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('/tmp/test_backup', $output);
    }

    public function testOptionSince(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $courseRepository->method('findUpdatedSince')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);

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
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $courseRepository->method('findAll')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--compress' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        // 检查压缩是否成功，或者压缩失败时的警告信息
        $this->assertMatchesRegularExpression('/备份文件已压缩|压缩失败/', $output);
    }

    public function testOptionIncludeMedia(): void
    {
        // 创建必要的 mock 对象
        $courseRepository = $this->createMock(CourseRepository::class);
        $courseRepository->method('findAll')->willReturn([]);

        // 注册 mock 服务到容器
        self::getContainer()->set(CourseRepository::class, $courseRepository);

        $commandTester = $this->getCommandTester();
        $commandTester->execute(['--include-media' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('备份媒体文件', $output);
    }
}
