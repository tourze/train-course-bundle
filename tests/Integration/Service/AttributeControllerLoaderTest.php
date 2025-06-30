<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Integration\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\TrainCourseBundle\Service\AttributeControllerLoader;
use Tourze\TrainCourseBundle\Tests\Integration\IntegrationTestKernel;

class AttributeControllerLoaderTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }
    
    public function test_service_exists(): void
    {
        self::bootKernel();
        
        $service = self::getContainer()->get(AttributeControllerLoader::class);
        
        $this->assertInstanceOf(AttributeControllerLoader::class, $service);
    }
}