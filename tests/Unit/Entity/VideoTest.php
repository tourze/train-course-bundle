<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Video;

class VideoTest extends TestCase
{
    public function test_video_entity_can_be_instantiated(): void
    {
        $video = new Video();
        
        $this->assertInstanceOf(Video::class, $video);
    }
    
    public function test_video_entity_has_id(): void
    {
        $video = new Video();
        
        $this->assertNull($video->getId());
    }
}