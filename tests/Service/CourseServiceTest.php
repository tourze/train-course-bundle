<?php

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\CourseService;

/**
 * CourseService 单元测试
 */
class CourseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Service 测试主要验证方法存在性
    }

    public function test_serviceExists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_getAllChildCategoriesMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getAllChildCategories'));
    }

    public function test_getLessonPlayUrlMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getLessonPlayUrl'));
    }

    public function test_getLessonArrayMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getLessonArray'));
    }

    public function test_getCourseWithDetailsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getCourseWithDetails'));
    }

    public function test_isCourseValidMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('isCourseValid'));
    }

    public function test_getCourseTotalDurationMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getCourseTotalDuration'));
    }

    public function test_getCourseTotalLessonsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getCourseTotalLessons'));
    }

    public function test_isSupportedVideoProtocolMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('isSupportedVideoProtocol'));
    }

    public function test_getCourseProgressMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('getCourseProgress'));
    }
} 