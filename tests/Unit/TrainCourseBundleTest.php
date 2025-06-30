<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainCourseBundle\TrainCourseBundle;

class TrainCourseBundleTest extends TestCase
{
    public function test_bundle_can_be_instantiated(): void
    {
        $bundle = new TrainCourseBundle();
        
        $this->assertInstanceOf(TrainCourseBundle::class, $bundle);
    }
    
    public function test_bundle_can_build_container(): void
    {
        $bundle = new TrainCourseBundle();
        $container = new ContainerBuilder();
        
        $bundle->build($container);
        
        $this->assertTrue(true); // If no exception thrown, build was successful
    }
}