<?php

namespace Tourze\TrainCourseBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Command\CourseCleanupCommand;

/**
 * CourseCleanupCommand 单元测试
 */
class CourseCleanupCommandTest extends TestCase
{
    protected function setUp(): void
    {
        // Command 测试主要验证方法存在性
    }

    public function test_commandExists(): void
    {
        $reflection = new \ReflectionClass(CourseCleanupCommand::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_configureMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseCleanupCommand::class);
        $this->assertTrue($reflection->hasMethod('configure'));
    }

    public function test_executeMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseCleanupCommand::class);
        $this->assertTrue($reflection->hasMethod('execute'));
    }

    public function test_commandInheritsFromCommand(): void
    {
        $reflection = new \ReflectionClass(CourseCleanupCommand::class);
        $this->assertTrue($reflection->isSubclassOf('Symfony\Component\Console\Command\Command'));
    }

    public function test_methodVisibility(): void
    {
        $reflection = new \ReflectionClass(CourseCleanupCommand::class);
        
        // 验证方法可见性
        $configureMethod = $reflection->getMethod('configure');
        $this->assertTrue($configureMethod->isProtected());
        
        $executeMethod = $reflection->getMethod('execute');
        $this->assertTrue($executeMethod->isProtected());
    }
} 