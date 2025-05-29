<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\CoursePlayControlRepository;

/**
 * CoursePlayControlRepository 单元测试
 */
class CoursePlayControlRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findEnabledControlsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('findEnabledControls'));
    }

    public function test_findStrictModeControlsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('findStrictModeControls'));
    }

    public function test_findWithWatermarkEnabledMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('findWithWatermarkEnabled'));
    }

    public function test_findByMaxDeviceCountMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('findByMaxDeviceCount'));
    }

    public function test_getPlayControlStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('getPlayControlStatistics'));
    }

    public function test_findNeedingAuthUpdateMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlRepository::class);
        $this->assertTrue($reflection->hasMethod('findNeedingAuthUpdate'));
    }
} 