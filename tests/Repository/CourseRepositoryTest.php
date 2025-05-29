<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Repository\CourseRepository;

/**
 * CourseRepository 单元测试
 */
class CourseRepositoryTest extends TestCase
{
    public function test_repositoryExists(): void
    {
        $this->assertTrue(class_exists(CourseRepository::class));
    }

    public function test_repositoryHasCorrectMethods(): void
    {
        $reflection = new \ReflectionClass(CourseRepository::class);
        
        $this->assertTrue($reflection->hasMethod('findValidCourses'));
        $this->assertTrue($reflection->hasMethod('findByCategory'));
        $this->assertTrue($reflection->hasMethod('searchCourses'));
        $this->assertTrue($reflection->hasMethod('getStatistics'));
        $this->assertTrue($reflection->hasMethod('createBaseQueryBuilder'));
        $this->assertTrue($reflection->hasMethod('findByPriceRange'));
    }

    public function test_repositoryMethodsHaveCorrectReturnTypes(): void
    {
        $reflection = new \ReflectionClass(CourseRepository::class);
        
        $findValidCoursesMethod = $reflection->getMethod('findValidCourses');
        $this->assertSame('array', $findValidCoursesMethod->getReturnType()->getName());
        
        $findByCategoryMethod = $reflection->getMethod('findByCategory');
        $this->assertSame('array', $findByCategoryMethod->getReturnType()->getName());
        
        $searchCoursesMethod = $reflection->getMethod('searchCourses');
        $this->assertSame('array', $searchCoursesMethod->getReturnType()->getName());
        
        $getStatisticsMethod = $reflection->getMethod('getStatistics');
        $this->assertSame('array', $getStatisticsMethod->getReturnType()->getName());
    }
} 