<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\CoursePlayControlService;

/**
 * CoursePlayControlService 集成测试
 *
 * @internal
 */
#[CoversClass(CoursePlayControlService::class)]
final class CoursePlayControlServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
    }

    public function testServiceExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testGetPlayControlConfigMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getPlayControlConfig'));
    }

    public function testCreateOrUpdatePlayControlMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('createOrUpdatePlayControl'));
    }

    public function testEnableStrictModeMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('enableStrictMode'));
    }

    public function testCanFastForwardMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('canFastForward'));
    }

    public function testCanControlSpeedMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('canControlSpeed'));
    }

    public function testGetAllowedSpeedsMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getAllowedSpeeds'));
    }

    public function testGetWatermarkConfigMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getWatermarkConfig'));
    }

    public function testIsStrictModeMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('isStrictMode'));
    }

    public function testGeneratePlayAuthMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('generatePlayAuth'));
    }

    public function testValidatePlayAuthMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('validatePlayAuth'));
    }

    public function testGetPlayControlStatisticsMethodExists(): void
    {
        $reflection = new \ReflectionClass(CoursePlayControlService::class);
        $this->assertTrue($reflection->hasMethod('getPlayControlStatistics'));
    }
}
