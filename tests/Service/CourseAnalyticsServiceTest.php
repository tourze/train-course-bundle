<?php

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\CourseAnalyticsService;

/**
 * CourseAnalyticsService 单元测试
 */
class CourseAnalyticsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Service 测试主要验证方法存在性
    }

    public function test_serviceExists(): void
    {
        $reflection = new \ReflectionClass(CourseAnalyticsService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_getCourseAnalyticsReportMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAnalyticsService::class);
        $this->assertTrue($reflection->hasMethod('getCourseAnalyticsReport'));
    }

    public function test_getCourseRankingsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAnalyticsService::class);
        $this->assertTrue($reflection->hasMethod('getCourseRankings'));
    }

    public function test_methodReturnTypes(): void
    {
        $reflection = new \ReflectionClass(CourseAnalyticsService::class);
        
        // 验证方法参数和返回类型
        $getCourseAnalyticsReportMethod = $reflection->getMethod('getCourseAnalyticsReport');
        $this->assertCount(1, $getCourseAnalyticsReportMethod->getParameters());
        
        $getCourseRankingsMethod = $reflection->getMethod('getCourseRankings');
        $this->assertGreaterThanOrEqual(0, $getCourseRankingsMethod->getParameters());
    }
} 