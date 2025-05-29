<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;

/**
 * CourseOutlineRepository 单元测试
 */
class CourseOutlineRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findPublishedByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        $this->assertTrue($reflection->hasMethod('findPublishedByCourse'));
    }

    public function test_findByStatusMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        $this->assertTrue($reflection->hasMethod('findByStatus'));
    }

    public function test_searchOutlinesMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        $this->assertTrue($reflection->hasMethod('searchOutlines'));
    }

    public function test_getOutlineStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        $this->assertTrue($reflection->hasMethod('getOutlineStatistics'));
    }

    public function test_methodReturnTypes(): void
    {
        $reflection = new \ReflectionClass(CourseOutlineRepository::class);
        
        // 验证方法参数和返回类型
        $findByCourseMethod = $reflection->getMethod('findByCourse');
        $this->assertCount(1, $findByCourseMethod->getParameters());
        
        $findPublishedByCourseMethod = $reflection->getMethod('findPublishedByCourse');
        $this->assertCount(1, $findPublishedByCourseMethod->getParameters());
        
        $findByStatusMethod = $reflection->getMethod('findByStatus');
        $this->assertGreaterThanOrEqual(1, $findByStatusMethod->getParameters());
        
        $searchOutlinesMethod = $reflection->getMethod('searchOutlines');
        $this->assertGreaterThanOrEqual(1, $searchOutlinesMethod->getParameters());
        
        $getOutlineStatisticsMethod = $reflection->getMethod('getOutlineStatistics');
        $this->assertCount(1, $getOutlineStatisticsMethod->getParameters());
    }
} 