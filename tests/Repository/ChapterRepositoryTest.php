<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;

/**
 * ChapterRepository 单元测试
 */
class ChapterRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(ChapterRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(ChapterRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findByCourseWithLessonsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(ChapterRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourseWithLessons'));
    }

    public function test_getChapterStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(ChapterRepository::class);
        $this->assertTrue($reflection->hasMethod('getChapterStatistics'));
    }

    public function test_searchChaptersMethod_exists(): void
    {
        $reflection = new \ReflectionClass(ChapterRepository::class);
        $this->assertTrue($reflection->hasMethod('searchChapters'));
    }
} 