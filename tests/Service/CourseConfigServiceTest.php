<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * CourseConfigService 集成测试
 *
 * @internal
 */
#[CoversClass(CourseConfigService::class)]
#[RunTestsInSeparateProcesses]
final class CourseConfigServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testServiceExists(): void
    {
        // 验证服务类可以实例化
        $reflection = new \ReflectionClass(CourseConfigService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testGet(): void
    {
        $service = self::getService(CourseConfigService::class);

        // 测试获取存在的配置参数（这些参数在实际环境中可能不存在，所以会返回默认值）
        $result = $service->get('train_course.video.play_url_cache_time', 30);
        $this->assertIsInt($result);
        $this->assertEquals(30, $result); // 应该返回默认值

        // 测试获取不存在的配置参数，返回默认值
        $defaultValue = 'test_default';
        $result = $service->get('non_existent_key', $defaultValue);
        $this->assertEquals($defaultValue, $result);

        // 测试获取不存在的配置参数，无默认值
        $result = $service->get('non_existent_key');
        $this->assertNull($result);

        // 测试获取字符串类型配置
        $result = $service->get('train_course.course.default_cover', '/default.jpg');
        $this->assertIsString($result);
        $this->assertEquals('/default.jpg', $result);

        // 测试获取数组类型配置
        $defaultArray = ['image/jpeg', 'image/png'];
        $result = $service->get('train_course.course.cover_allowed_types', $defaultArray);
        $this->assertIsArray($result);
        $this->assertEquals($defaultArray, $result);

        // 测试获取布尔类型配置
        $result = $service->get('train_course.features.auto_audit', false);
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }
}
