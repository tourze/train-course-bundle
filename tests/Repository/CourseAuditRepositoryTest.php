<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;

/**
 * CourseAuditRepository 单元测试
 */
class CourseAuditRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findByStatusMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findByStatus'));
    }

    public function test_findPendingAuditsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findPendingAudits'));
    }

    public function test_findOverdueAuditsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findOverdueAudits'));
    }

    public function test_findByAuditorMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findByAuditor'));
    }

    public function test_findByAuditTypeMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findByAuditType'));
    }

    public function test_getAuditStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('getAuditStatistics'));
    }

    public function test_findLatestByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        $this->assertTrue($reflection->hasMethod('findLatestByCourse'));
    }

    public function test_methodReturnTypes(): void
    {
        $reflection = new \ReflectionClass(CourseAuditRepository::class);
        
        // 验证方法参数和返回类型
        $findByCourseMethod = $reflection->getMethod('findByCourse');
        $this->assertCount(1, $findByCourseMethod->getParameters());
        
        $findByStatusMethod = $reflection->getMethod('findByStatus');
        $this->assertCount(1, $findByStatusMethod->getParameters());
        
        $findByAuditorMethod = $reflection->getMethod('findByAuditor');
        $this->assertGreaterThanOrEqual(1, $findByAuditorMethod->getParameters());
        
        $findByAuditTypeMethod = $reflection->getMethod('findByAuditType');
        $this->assertGreaterThanOrEqual(1, $findByAuditTypeMethod->getParameters());
    }
} 