<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\TrainCourseBundle\DependencyInjection\TrainCourseExtension;

/**
 * @internal
 */
#[CoversClass(TrainCourseExtension::class)]
final class TrainCourseExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 集成测试设置
    }

    public function testExtensionLoadsSuccessfully(): void
    {
        $extension = new TrainCourseExtension();
        $container = new ContainerBuilder();

        // 设置必要的参数
        $container->setParameter('kernel.environment', 'test');

        $extension->load([], $container);

        // 验证资源被正确加载，会有Tourze\TrainCourseBundle\Service\下的服务
        $this->assertTrue($container->hasDefinition('Tourze\TrainCourseBundle\Service\CourseService'));
    }

    public function testLoadMethod(): void
    {
        $extension = new TrainCourseExtension();
        $container = new ContainerBuilder();

        // 设置必要的参数
        $container->setParameter('kernel.environment', 'test');

        $configs = [
            ['enabled' => true],
        ];

        $extension->load($configs, $container);

        $this->assertGreaterThan(0, count($container->getDefinitions()));
    }
}
