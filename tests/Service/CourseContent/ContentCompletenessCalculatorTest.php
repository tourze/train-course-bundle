<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;
use Tourze\TrainCourseBundle\Service\CourseContent\ContentCompletenessCalculator;

/**
 * @internal
 */
#[CoversClass(ContentCompletenessCalculator::class)]
final class ContentCompletenessCalculatorTest extends TestCase
{
    private ContentCompletenessCalculator $calculator;

    private ChapterRepository $chapterRepository;

    private CourseOutlineRepository $outlineRepository;

    protected function setUp(): void
    {
        $this->chapterRepository = $this->createMock(ChapterRepository::class);
        $this->outlineRepository = $this->createMock(CourseOutlineRepository::class);
        $this->calculator = new ContentCompletenessCalculator(
            $this->chapterRepository,
            $this->outlineRepository
        );
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ContentCompletenessCalculator::class, $this->calculator);
    }

    public function testCalculateContentCompletenessWithEmptyCourse(): void
    {
        $course = $this->createBasicCourse();

        $this->chapterRepository->method('findByCourse')
            ->willReturn([])
        ;

        $this->outlineRepository->method('findByCourse')
            ->willReturn([])
        ;

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

        $this->chapterRepository->method('findByCourse')
            ->willReturn([])
        ;

        $this->outlineRepository->method('findByCourse')
            ->willReturn([])
        ;

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertGreaterThan(0, $result['score']);
        $this->assertIsArray($result['details']);
        $this->assertArrayHasKey('basic_info', $result['details']);
        $this->assertGreaterThan(0, $result['details']['basic_info']);
    }

    public function testCalculateContentCompletenessWithChaptersAndLessons(): void
    {
        $course = $this->createCompleteCourse();
        $chapter = $this->createChapterWithLessons();

        $this->chapterRepository->method('findByCourse')
            ->willReturn([$chapter])
        ;

        $this->outlineRepository->method('findByCourse')
            ->willReturn([])
        ;

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertIsArray($result['details']);
        $this->assertArrayHasKey('chapters_lessons', $result['details']);
        $this->assertGreaterThan(0, $result['details']['chapters_lessons']);
    }

    public function testCalculateContentCompletenessWithOutlines(): void
    {
        $course = $this->createCompleteCourse();
        $outline = $this->createOutline();

        $this->chapterRepository->method('findByCourse')
            ->willReturn([])
        ;

        $this->outlineRepository->method('findByCourse')
            ->willReturn([$outline])
        ;

        $this->outlineRepository->method('findPublishedByCourse')
            ->willReturn([$outline])
        ;

        $result = $this->calculator->calculateContentCompleteness($course);

        $this->assertIsArray($result['details']);
        $this->assertArrayHasKey('outlines', $result['details']);
        $this->assertGreaterThan(0, $result['details']['outlines']);
    }

    public function testCalculateContentCompletenessScoreDoesNotExceed100(): void
    {
        $course = $this->createCompleteCourse();
        $chapter = $this->createChapterWithLessons();
        $outline = $this->createOutline();

        $this->chapterRepository->method('findByCourse')
            ->willReturn([$chapter])
        ;

        $this->outlineRepository->method('findByCourse')
            ->willReturn([$outline])
        ;

        $this->outlineRepository->method('findPublishedByCourse')
            ->willReturn([$outline])
        ;

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

    /**
     * 创建带有课时的章节
     */
    private function createChapterWithLessons(): object
    {
        return new class {
            /**
             * @return ArrayCollection<int, object>
             */
            public function getLessons(): ArrayCollection
            {
                $lesson = new class {
                    public function getTitle(): string
                    {
                        return '课时1';
                    }
                };

                /** @var ArrayCollection<int, object> */
                return new ArrayCollection([$lesson]);
            }
        };
    }

    /**
     * 创建大纲
     */
    private function createOutline(): object
    {
        return new class {
            public function getTitle(): string
            {
                return '大纲1';
            }
        };
    }
}
