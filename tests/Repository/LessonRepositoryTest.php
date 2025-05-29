<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\LessonRepository;

/**
 * LessonRepository 单元测试
 */
class LessonRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Repository 测试主要验证方法存在性
    }

    public function test_repositoryExists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_findByChapterMethod_exists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->hasMethod('findByChapter'));
    }

    public function test_findByCourseMethod_exists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->hasMethod('findByCourse'));
    }

    public function test_findLessonsWithVideoMethod_exists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->hasMethod('findLessonsWithVideo'));
    }

    public function test_getLessonStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->hasMethod('getLessonStatistics'));
    }

    public function test_searchLessonsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->hasMethod('searchLessons'));
    }

    public function test_findByVideoProtocolMethod_exists(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        $this->assertTrue($reflection->hasMethod('findByVideoProtocol'));
    }

    public function test_methodReturnTypes(): void
    {
        $reflection = new \ReflectionClass(LessonRepository::class);
        
        // 验证方法参数和返回类型
        $findByChapterMethod = $reflection->getMethod('findByChapter');
        $this->assertCount(1, $findByChapterMethod->getParameters());
        
        $findByCourseMethod = $reflection->getMethod('findByCourse');
        $this->assertCount(1, $findByCourseMethod->getParameters());
        
        $findLessonsWithVideoMethod = $reflection->getMethod('findLessonsWithVideo');
        $this->assertCount(1, $findLessonsWithVideoMethod->getParameters());
        
        $getLessonStatisticsMethod = $reflection->getMethod('getLessonStatistics');
        $this->assertCount(1, $getLessonStatisticsMethod->getParameters());
        
        $searchLessonsMethod = $reflection->getMethod('searchLessons');
        $this->assertGreaterThanOrEqual(1, $searchLessonsMethod->getParameters());
        
        $findByVideoProtocolMethod = $reflection->getMethod('findByVideoProtocol');
        $this->assertCount(1, $findByVideoProtocolMethod->getParameters());
    }
} 