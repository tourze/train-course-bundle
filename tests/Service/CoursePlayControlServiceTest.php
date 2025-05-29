<?php

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\CoursePlayControlService;

/**
 * CoursePlayControlService 单元测试
 */
class CoursePlayControlServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Service 测试主要验证方法存在性
    }

    public function test_serviceExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_getPlayControlConfigMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getPlayControlConfig'));
    }

    public function test_createOrUpdatePlayControlMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('createOrUpdatePlayControl'));
    }

    public function test_enableStrictModeMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('enableStrictMode'));
    }

    public function test_canFastForwardMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('canFastForward'));
    }

    public function test_canControlSpeedMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('canControlSpeed'));
    }

    public function test_getAllowedSpeedsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getAllowedSpeeds'));
    }

    public function test_getWatermarkConfigMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getWatermarkConfig'));
    }

    public function test_isStrictModeMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('isStrictMode'));
    }

    public function test_generatePlayAuthMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('generatePlayAuth'));
    }

    public function test_validatePlayAuthMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('validatePlayAuth'));
    }

    public function test_getPlayControlStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getPlayControlStatistics'));
    }
} 