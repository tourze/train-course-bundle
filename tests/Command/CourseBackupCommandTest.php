<?php

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Command\CourseBackupCommand;

/**
 * CourseBackupCommand 单元测试
 */
class CourseBackupCommandTest extends TestCase
{
    public function test_commandExists(): void
    {
        $reflection = new \ReflectionClass(CourseBackupCommand::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_configureMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseBackupCommand::class);
        $this->assertTrue($reflection->hasMethod('configure'));
    }

    public function test_executeMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseBackupCommand::class);
        $this->assertTrue($reflection->hasMethod('execute'));
    }

    public function test_commandInheritsFromCommand(): void
    {
        $reflection = new \ReflectionClass(CourseBackupCommand::class);
        $this->assertTrue($reflection->isSubclassOf('Symfony\Component\Console\Command\Command'));
    }

    public function test_methodVisibility(): void
    {
        $reflection = new \ReflectionClass(CourseBackupCommand::class);

        // 验证方法可见性
        $configureMethod = $reflection->getMethod('configure');
        $this->assertTrue($configureMethod->isProtected());

        $executeMethod = $reflection->getMethod('execute');
        $this->assertTrue($executeMethod->isProtected());
    }
}
