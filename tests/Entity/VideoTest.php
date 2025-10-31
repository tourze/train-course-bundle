<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Video;

/**
 * @internal
 */
#[CoversClass(Video::class)]
final class VideoTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Video();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
    }

    public function testVideoEntityCanBeInstantiated(): void
    {
        $video = new Video();

        // 验证实体可以转换为字符串
        $stringValue = (string) $video;
        $this->assertSame('', $stringValue); // 新实例应该返回空字符串

        // 验证初始状态
        $this->assertNull($video->getId());
        $this->assertNull($video->getTitle());
        $this->assertNull($video->getVideoId());
        $this->assertNull($video->getSize());
        $this->assertNull($video->getDuration());
        $this->assertNull($video->getCoverUrl());
    }

    public function testVideoEntityHasId(): void
    {
        $video = new Video();

        $this->assertNull($video->getId());
    }
}
