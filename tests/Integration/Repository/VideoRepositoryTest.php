<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Integration\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\TrainCourseBundle\Repository\VideoRepository;
use Tourze\TrainCourseBundle\Tests\Integration\IntegrationTestKernel;

class VideoRepositoryTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }
    
    public function test_repository_exists(): void
    {
        self::bootKernel();
        
        $repository = self::getContainer()->get(VideoRepository::class);
        
        $this->assertInstanceOf(VideoRepository::class, $repository);
    }
}