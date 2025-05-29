<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;

/**
 * CourseVersionRepository 单元测试
 */
class CourseVersionRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findCurrentByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findCurrentByCourse'));
    }

    public function test_findByVersionMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findByVersion'));
    }

    public function test_findByStatusMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findByStatus'));
    }

    public function test_findPublishedByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findPublishedByCourse'));
    }

    public function test_findLatestByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findLatestByCourse'));
    }

    public function test_getVersionStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('getVersionStatistics'));
    }

    public function test_searchVersionsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('searchVersions'));
    }

    public function test_findVersionsToArchiveMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseVersionRepository::class);
        $this->assertTrue($reflection->hasMethod('findVersionsToArchive'));
    }
} 