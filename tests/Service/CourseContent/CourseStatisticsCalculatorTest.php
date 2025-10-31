<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;
use Tourze\TrainCourseBundle\Service\CourseContent\ContentCompletenessCalculator;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseStatisticsCalculator;

/**
 * @internal
 */
#[CoversClass(CourseStatisticsCalculator::class)]
final class CourseStatisticsCalculatorTest extends TestCase
{
    private CourseStatisticsCalculator $calculator;

    private ChapterRepository $chapterRepository;

    private CourseOutlineRepository $outlineRepository;

    private ContentCompletenessCalculator $completenessCalculator;

    protected function setUp(): void
    {
        $this->chapterRepository = $this->createMock(ChapterRepository::class);
        $this->outlineRepository = $this->createMock(CourseOutlineRepository::class);
        $this->completenessCalculator = $this->createMock(ContentCompletenessCalculator::class);

        $this->calculator = new CourseStatisticsCalculator(
            $this->chapterRepository,
            $this->outlineRepository,
            $this->completenessCalculator
        );
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseStatisticsCalculator::class, $this->calculator);
    }

    public function testGetCourseContentStatisticsReturnsArray(): void
    {
        $course = new Course();

        $this->chapterRepository->method('getChapterStatistics')
            ->willReturn([])
        ;

        $this->outlineRepository->method('getOutlineStatistics')
            ->willReturn([])
        ;

        $this->completenessCalculator->method('calculateContentCompleteness')
            ->willReturn([
                'score' => 50,
                'percentage' => 50.0,
                'details' => [],
            ])
        ;

        $result = $this->calculator->getCourseContentStatistics($course);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('chapters', $result);
        $this->assertArrayHasKey('lessons', $result);
        $this->assertArrayHasKey('outlines', $result);
        $this->assertArrayHasKey('content_completeness', $result);
    }

    public function testGetCourseContentStatisticsIncludesChapterStats(): void
    {
        $course = new Course();
        $chapterStats = [
            'total' => 10,
            'published' => 8,
        ];

        $this->chapterRepository->method('getChapterStatistics')
            ->willReturn($chapterStats)
        ;

        $this->outlineRepository->method('getOutlineStatistics')
            ->willReturn([])
        ;

        $this->completenessCalculator->method('calculateContentCompleteness')
            ->willReturn([
                'score' => 50,
                'percentage' => 50.0,
                'details' => [],
            ])
        ;

        $result = $this->calculator->getCourseContentStatistics($course);

        $this->assertSame($chapterStats, $result['chapters']);
    }

    public function testGetCourseContentStatisticsIncludesOutlineStats(): void
    {
        $course = new Course();
        $outlineStats = [
            'total' => 5,
            'published' => 4,
        ];

        $this->chapterRepository->method('getChapterStatistics')
            ->willReturn([])
        ;

        $this->outlineRepository->method('getOutlineStatistics')
            ->willReturn($outlineStats)
        ;

        $this->completenessCalculator->method('calculateContentCompleteness')
            ->willReturn([
                'score' => 50,
                'percentage' => 50.0,
                'details' => [],
            ])
        ;

        $result = $this->calculator->getCourseContentStatistics($course);

        $this->assertSame($outlineStats, $result['outlines']);
    }

    public function testGetCourseContentStatisticsIncludesCompleteness(): void
    {
        $course = new Course();
        $completeness = [
            'score' => 75,
            'percentage' => 75.0,
            'details' => [
                'basic_info' => 20,
                'chapters_lessons' => 40,
                'outlines' => 15,
            ],
        ];

        $this->chapterRepository->method('getChapterStatistics')
            ->willReturn([])
        ;

        $this->outlineRepository->method('getOutlineStatistics')
            ->willReturn([])
        ;

        $this->completenessCalculator->method('calculateContentCompleteness')
            ->willReturn($completeness)
        ;

        $result = $this->calculator->getCourseContentStatistics($course);

        $this->assertSame($completeness, $result['content_completeness']);
    }

    public function testGetCourseContentStatisticsLessonStatsIsEmpty(): void
    {
        $course = new Course();

        $this->chapterRepository->method('getChapterStatistics')
            ->willReturn([])
        ;

        $this->outlineRepository->method('getOutlineStatistics')
            ->willReturn([])
        ;

        $this->completenessCalculator->method('calculateContentCompleteness')
            ->willReturn([
                'score' => 50,
                'percentage' => 50.0,
                'details' => [],
            ])
        ;

        $result = $this->calculator->getCourseContentStatistics($course);

        $this->assertIsArray($result['lessons']);
        $this->assertEmpty($result['lessons']);
    }
}
