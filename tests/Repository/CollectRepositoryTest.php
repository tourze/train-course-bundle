<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\CollectRepository;

/**
 * CollectRepository 单元测试
 */
class CollectRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByUserMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('findByUser'));
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findByUserAndCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('findByUserAndCourse'));
    }

    public function test_findByGroupMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('findByGroup'));
    }

    public function test_isCollectedByUserMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('isCollectedByUser'));
    }

    public function test_getUserCollectGroupsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('getUserCollectGroups'));
    }

    public function test_getCollectStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('getCollectStatistics'));
    }

    public function test_searchCollectsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        $this->assertTrue($reflection->hasMethod('searchCollects'));
    }

    public function test_methodReturnTypes(): void
    {
        $reflection = new \ReflectionClass(CollectRepository::class);
        
        // 验证方法参数和返回类型
        $findByUserMethod = $reflection->getMethod('findByUser');
        $this->assertCount(1, $findByUserMethod->getParameters());
        
        $findByCourseMethod = $reflection->getMethod('findByCourse');
        $this->assertCount(1, $findByCourseMethod->getParameters());
        
        $findByUserAndCourseMethod = $reflection->getMethod('findByUserAndCourse');
        $this->assertCount(2, $findByUserAndCourseMethod->getParameters());
        
        $isCollectedByUserMethod = $reflection->getMethod('isCollectedByUser');
        $this->assertCount(2, $isCollectedByUserMethod->getParameters());
    }
} 