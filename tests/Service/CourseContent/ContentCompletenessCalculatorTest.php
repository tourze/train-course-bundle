<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Service\CourseContent\ContentCompletenessCalculator;

/**
 * @internal
 */
#[CoversClass(ContentCompletenessCalculator::class)]
final class ContentCompletenessCalculatorTest extends TestCase
{
    private ContentCompletenessCalculator $calculator;

    protected function setUp(): void
    {
        // 使用真实的Repository实例，避免Mock final类
        $chapterRepository = $this->createRepositoryStub();
        $outlineRepository = $this->createRepositoryStub();

        $this->calculator = new ContentCompletenessCalculator(
            $chapterRepository,
            $outlineRepository
        );
    }

    /**
     * 创建Repository存根，避免final类的Mock问题
     */
    private function createRepositoryStub()
    {
        return new class {
            public function findByCourse($course) {
                return [];
            }
            public function findPublishedByCourse($course) {
                return [];
            }
        };
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ContentCompletenessCalculator::class, $this->calculator);
    }

    public function testCalculateContentCompletenessWithEmptyCourse(): void
    {
        $course = $this->createBasicCourse();

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('percentage', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertLessThanOrEqual(100, $result['score']);
    }

    public function testCalculateContentCompletenessWithBasicInfo(): void
    {
        $course = $this->createCompleteCourse();

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertGreaterThan(0, $result['score']);
        $this->assertIsArray($result['details']);
        $this->assertArrayHasKey('basic_info', $result['details']);
        $this->assertGreaterThan(0, $result['details']['basic_info']);
    }

    public function testCalculateContentCompletenessWithChaptersAndLessons(): void
    {
        $course = $this->createCompleteCourse();

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertIsArray($result['details']);
        $this->assertArrayHasKey('chapters_lessons', $result['details']);
    }

    public function testCalculateContentCompletenessWithOutlines(): void
    {
        $course = $this->createCompleteCourse();

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertIsArray($result['details']);
        $this->assertArrayHasKey('outlines', $result['details']);
    }

    public function testCalculateContentCompletenessScoreDoesNotExceed100(): void
    {
        $course = $this->createCompleteCourse();

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertLessThanOrEqual(100, $result['score']);
        $this->assertLessThanOrEqual(100, $result['percentage']);
    }

    /**
     * 创建基本课程
     */
    private function createBasicCourse(): Course
    {
        $course = new Course();
        $course->setTitle('');

        return $course;
    }

    /**
     * 创建完整课程
     */
    private function createCompleteCourse(): Course
    {
        $course = new Course();
        $course->setTitle('完整课程');
        $course->setDescription('课程描述');
        $course->setCoverThumb('/path/to/cover.jpg');
        $course->setLearnHour(40);

        return $course;
    }
}
