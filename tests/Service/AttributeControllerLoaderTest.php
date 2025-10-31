<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testServiceExists(): void
    {
        $attributeControllerLoader = self::getService(AttributeControllerLoader::class);

        // 验证服务的公共方法存在
        $reflection = new \ReflectionClass($attributeControllerLoader);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $publicMethodNames = array_map(fn ($method) => $method->getName(), $publicMethods);

        $this->assertContains('autoload', $publicMethodNames);
        $this->assertContains('load', $publicMethodNames);
        $this->assertContains('supports', $publicMethodNames);
    }

    public function testAutoloadMethod(): void
    {
        $attributeControllerLoader = self::getService(AttributeControllerLoader::class);

        $collection = $attributeControllerLoader->autoload();
        $this->assertGreaterThanOrEqual(0, $collection->count());
        $this->assertIsArray($collection->all());
    }

    public function testLoadMethod(): void
    {
        $attributeControllerLoader = self::getService(AttributeControllerLoader::class);

        $collection = $attributeControllerLoader->load('resource');
        $this->assertGreaterThanOrEqual(0, $collection->count());
        $this->assertIsArray($collection->all());
    }

    public function testSupportsMethod(): void
    {
        $attributeControllerLoader = self::getService(AttributeControllerLoader::class);

        $this->assertFalse($attributeControllerLoader->supports('resource'));
    }
}
