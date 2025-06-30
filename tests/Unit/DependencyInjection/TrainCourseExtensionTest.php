<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainCourseBundle\DependencyInjection\TrainCourseExtension;

class TrainCourseExtensionTest extends TestCase
{
    public function test_extension_loads_successfully(): void
    {
        $extension = new TrainCourseExtension();
        $container = new ContainerBuilder();
        
        $extension->load([], $container);
        
        // 验证资源被正确加载，会有Tourze\TrainCourseBundle\Service\下的服务
        $this->assertTrue($container->hasDefinition('Tourze\\TrainCourseBundle\\Service\\CourseService'));
    }
}