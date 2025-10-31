<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * CourseConfigService 单元测试
 *
 * @internal
 */
#[CoversClass(CourseConfigService::class)]
#[RunTestsInSeparateProcesses]
final class CourseConfigServiceUnitTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testGetVideoPlayUrlCacheTime(): void
    {
        // 使用容器获取服务，而不是直接实例化
        $service = self::getService(CourseConfigService::class);
        $result = $service->getVideoPlayUrlCacheTime();

        // 验证默认值或配置值
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetVideoPlayUrlCacheTimeWithDefault(): void
    {
        $service = self::getService(CourseConfigService::class);
        $result = $service->getVideoPlayUrlCacheTime();

        // 验证返回合理的缓存时间
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetCourseInfoCacheTime(): void
    {
        $service = self::getService(CourseConfigService::class);
        $result = $service->getCourseInfoCacheTime();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetDefaultCourseValidDays(): void
    {
        $service = self::getService(CourseConfigService::class);
        $result = $service->getDefaultCourseValidDays();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetDefaultLearnHours(): void
    {
        $service = self::getService(CourseConfigService::class);
        $result = $service->getDefaultLearnHours();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetSupportedVideoProtocols(): void
    {
        $service = self::getService(CourseConfigService::class);
        $result = $service->getSupportedVideoProtocols();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testGetAllConfig(): void
    {
        $service = self::getService(CourseConfigService::class);
        $result = $service->getAllConfig();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('video', $result);
        $this->assertArrayHasKey('course', $result);
        $this->assertArrayHasKey('polyv', $result);
        $this->assertArrayHasKey('play_control', $result);
        $this->assertArrayHasKey('audit', $result);
    }
}
