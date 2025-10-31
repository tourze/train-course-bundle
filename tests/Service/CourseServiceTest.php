<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Service\CourseService;

/**
 * CourseService 集成测试
 *
 * @internal
 */
#[CoversClass(CourseService::class)]
#[RunTestsInSeparateProcesses]
final class CourseServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    private function getCourseService(): CourseService
    {
        return self::getService(CourseService::class);
    }

    public function testServiceExists(): void
    {
        $courseService = $this->getCourseService();
        $this->assertInstanceOf(CourseService::class, $courseService);
    }

    public function testFindByIdMethodExists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('findById'));
    }

    public function testFindOneByMethodExists(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->hasMethod('findOneBy'));
    }

    public function testServiceIsReadonly(): void
    {
        $reflection = new \ReflectionClass(CourseService::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testFindByIdWithNonExistentId(): void
    {
        $courseService = $this->getCourseService();
        $result = $courseService->findById('non-existent-id');
        $this->assertNull($result);
    }

    public function testFindOneByWithEmptyCriteria(): void
    {
        $courseService = $this->getCourseService();
        $result = $courseService->findOneBy([]);
        $this->assertInstanceOf(Course::class, $result);
    }

    public function testFindByWithEmptyCriteria(): void
    {
        $courseService = $this->getCourseService();
        $result = $courseService->findBy([]);
        $this->assertIsArray($result);
    }

    public function testGetCourseTotalDurationWithEmptyChapters(): void
    {
        $courseService = $this->getCourseService();
        $course = new Course();
        $duration = $courseService->getCourseTotalDuration($course);
        $this->assertSame(0, $duration);
    }

    public function testGetCourseTotalLessonsWithEmptyChapters(): void
    {
        $courseService = $this->getCourseService();
        $course = new Course();
        $lessons = $courseService->getCourseTotalLessons($course);
        $this->assertSame(0, $lessons);
    }

    public function testIsSupportedVideoProtocol(): void
    {
        $courseService = $this->getCourseService();
        $this->assertIsBool($courseService->isSupportedVideoProtocol('http://example.com'));
    }

    public function testGetCourseProgress(): void
    {
        $courseService = $this->getCourseService();
        $course = new Course();
        $progress = $courseService->getCourseProgress($course, 'user-123');

        $this->assertIsArray($progress);
        $this->assertArrayHasKey('course_id', $progress);
        $this->assertArrayHasKey('user_id', $progress);
        $this->assertArrayHasKey('total_lessons', $progress);
        $this->assertArrayHasKey('completed_lessons', $progress);
        $this->assertArrayHasKey('progress_percentage', $progress);
        $this->assertArrayHasKey('total_duration', $progress);
        $this->assertArrayHasKey('watched_duration', $progress);
    }
}
