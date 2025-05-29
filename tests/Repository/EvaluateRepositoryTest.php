<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;

/**
 * EvaluateRepository 单元测试
 */
class EvaluateRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findByUserMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findByUser'));
    }

    public function test_findByRatingMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findByRating'));
    }

    public function test_findByStatusMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findByStatus'));
    }

    public function test_getAverageRatingMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('getAverageRating'));
    }

    public function test_getEvaluateStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('getEvaluateStatistics'));
    }

    public function test_findPopularEvaluatesMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findPopularEvaluates'));
    }

    public function test_searchEvaluatesMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('searchEvaluates'));
    }

    public function test_findLatestEvaluatesMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findLatestEvaluates'));
    }

    public function test_findPendingEvaluatesMethod_exists(): void
    {
        $reflection = new \ReflectionClass(EvaluateRepository::class);
        $this->assertTrue($reflection->hasMethod('findPendingEvaluates'));
    }
} 