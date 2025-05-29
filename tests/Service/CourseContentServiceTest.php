<?php

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\CourseContentService;

/**
 * CourseContentService 单元测试
 */
class CourseContentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Service 测试主要验证方法存在性
    }

    public function test_serviceExists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_getCourseContentStructureMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('getCourseContentStructure'));
    }

    public function test_getCourseContentStatisticsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('getCourseContentStatistics'));
    }

    public function test_createChapterMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('createChapter'));
    }

    public function test_createLessonMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('createLesson'));
    }

    public function test_createOutlineMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('createOutline'));
    }

    public function test_batchImportContentMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('batchImportContent'));
    }

    public function test_reorderChaptersMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('reorderChapters'));
    }

    public function test_reorderLessonsMethod_exists(): void
    {
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->hasMethod('reorderLessons'));
    }
} 