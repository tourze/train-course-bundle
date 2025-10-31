<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\CollectRepository;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;
use Tourze\TrainCourseBundle\Service\CourseAnalyticsService;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsCollector;

/**
 * @internal
 */
#[CoversClass(StatisticsCollector::class)]
final class StatisticsCollectorTest extends TestCase
{
    private MockObject&CourseRepository $courseRepository;

    private MockObject&CollectRepository $collectRepository;

    private MockObject&EvaluateRepository $evaluateRepository;

    private MockObject&CourseAuditRepository $auditRepository;

    private MockObject&CourseAnalyticsService $analyticsService;

    private StatisticsCollector $collector;

    protected function setUp(): void
    {
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->collectRepository = $this->createMock(CollectRepository::class);
        $this->evaluateRepository = $this->createMock(EvaluateRepository::class);
        $this->auditRepository = $this->createMock(CourseAuditRepository::class);
        $this->analyticsService = $this->createMock(CourseAnalyticsService::class);

        $this->collector = new StatisticsCollector(
            $this->courseRepository,
            $this->collectRepository,
            $this->evaluateRepository,
            $this->auditRepository,
            $this->analyticsService
        );
    }

    public function testCollectStatisticsBasic(): void
    {
        $this->setupBasicRepositoryMocks();

        $result = $this->collector->collectStatistics(false, 10);

        // Method signature guarantees array return
        $this->assertArrayHasKey('basic', $result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertArrayHasKey('engagement', $result);
        $this->assertArrayHasKey('audit', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayNotHasKey('detailed', $result);
    }

    public function testCollectStatisticsDetailed(): void
    {
        $this->setupBasicRepositoryMocks();
        $this->setupDetailedRepositoryMocks();

        $result = $this->collector->collectStatistics(true, 5);

        // Method signature guarantees array return
        $this->assertArrayHasKey('basic', $result);
        $this->assertArrayHasKey('detailed', $result);
        $this->assertIsArray($result['detailed']);
        $this->assertArrayHasKey('top_courses', $result['detailed']);
        $this->assertArrayHasKey('category_stats', $result['detailed']);
        $this->assertArrayHasKey('monthly_trends', $result['detailed']);
    }

    public function testBasicStatisticsCalculation(): void
    {
        // count() is called twice: once in getBasicStatistics and once in calculateEngagementRate
        $this->courseRepository
            ->expects($this->exactly(2))
            ->method('count')
            ->with([])
            ->willReturn(100)
        ;

        $this->courseRepository
            ->expects($this->once())
            ->method('findValidCourses')
            ->willReturn(array_fill(0, 85, $this->createMock(Course::class)))
        ;

        $this->collectRepository
            ->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(250)
        ;

        $this->evaluateRepository
            ->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(180)
        ;

        $this->courseRepository
            ->expects($this->once())
            ->method('getStatistics')
            ->willReturn(['active' => 85, 'inactive' => 15])
        ;

        $this->collectRepository
            ->expects($this->once())
            ->method('getCollectStatistics')
            ->willReturn(['total' => 250, 'by_course' => []])
        ;

        $this->evaluateRepository
            ->expects($this->once())
            ->method('getEvaluateStatistics')
            ->willReturn(['total' => 180, 'by_course' => []])
        ;

        $this->auditRepository
            ->expects($this->once())
            ->method('getAuditStatistics')
            ->willReturn(['pending' => 5, 'approved' => 95])
        ;

        $result = $this->collector->collectStatistics(false, 10);

        $this->assertIsArray($result['basic']);
        $this->assertSame(100, $result['basic']['total_courses']);
        $this->assertSame(85, $result['basic']['valid_courses']);
        $this->assertSame(15, $result['basic']['invalid_courses']);
        $this->assertSame(250, $result['basic']['total_collects']);
        $this->assertSame(180, $result['basic']['total_evaluates']);
        $this->assertSame(2.5, $result['basic']['average_collects_per_course']);
        $this->assertSame(1.8, $result['basic']['average_evaluates_per_course']);
    }

    public function testBasicStatisticsWithZeroCourses(): void
    {
        // count() is called twice: once in getBasicStatistics and once in calculateEngagementRate
        $this->courseRepository
            ->expects($this->exactly(2))
            ->method('count')
            ->with([])
            ->willReturn(0)
        ;

        $this->courseRepository
            ->expects($this->once())
            ->method('findValidCourses')
            ->willReturn([])
        ;

        $this->collectRepository
            ->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(0)
        ;

        $this->evaluateRepository
            ->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(0)
        ;

        $this->setupMinimalStatisticsMocks();

        $result = $this->collector->collectStatistics(false, 10);

        $this->assertIsArray($result['basic']);
        $this->assertSame(0, $result['basic']['total_courses']);
        $this->assertSame(0, $result['basic']['average_collects_per_course']);
        $this->assertSame(0, $result['basic']['average_evaluates_per_course']);
    }

    public function testTopCoursesRetrieval(): void
    {
        $this->setupBasicRepositoryMocks();

        $course1 = $this->createMock(Course::class);
        $course1->method('getId')->willReturn('1');
        $course1->method('getTitle')->willReturn('Course 1');

        $course2 = $this->createMock(Course::class);
        $course2->method('getId')->willReturn('2');
        $course2->method('getTitle')->willReturn('Course 2');

        $this->analyticsService
            ->expects($this->once())
            ->method('getCourseRankings')
            ->with([
                'sort_by' => 'popularity_score',
                'limit' => 5,
            ])
            ->willReturn([
                [
                    'course' => $course1,
                    'popularity_score' => 95.5,
                    'quality_score' => 88.0,
                    'collect_count' => 100,
                    'evaluate_count' => 50,
                    'average_rating' => 4.5,
                ],
                [
                    'course' => $course2,
                    'popularity_score' => 85.0,
                    'quality_score' => 80.0,
                    'collect_count' => 75,
                    'evaluate_count' => 40,
                    'average_rating' => 4.2,
                ],
            ])
        ;

        $result = $this->collector->collectStatistics(true, 5);

        $this->assertIsArray($result['detailed']);
        $this->assertIsArray($result['detailed']['top_courses']);
        $this->assertCount(2, $result['detailed']['top_courses']);

        $topCourses = $result['detailed']['top_courses'];
        $this->assertIsArray($topCourses[0]);
        $this->assertSame('1', $topCourses[0]['id']);
        $this->assertSame('Course 1', $topCourses[0]['title']);
        $this->assertSame(95.5, $topCourses[0]['popularity_score']);
    }

    private function setupBasicRepositoryMocks(): void
    {
        $this->courseRepository
            ->method('count')
            ->willReturn(10)
        ;

        $this->courseRepository
            ->method('findValidCourses')
            ->willReturn(array_fill(0, 8, $this->createMock(Course::class)))
        ;

        $this->collectRepository
            ->method('count')
            ->willReturn(20)
        ;

        $this->evaluateRepository
            ->method('count')
            ->willReturn(15)
        ;

        $this->courseRepository
            ->method('getStatistics')
            ->willReturn(['active' => 8, 'inactive' => 2])
        ;

        $this->collectRepository
            ->method('getCollectStatistics')
            ->willReturn(['total' => 20, 'by_course' => []])
        ;

        $this->evaluateRepository
            ->method('getEvaluateStatistics')
            ->willReturn(['total' => 15, 'by_course' => []])
        ;

        $this->auditRepository
            ->method('getAuditStatistics')
            ->willReturn(['pending' => 1, 'approved' => 9])
        ;
    }

    private function setupDetailedRepositoryMocks(): void
    {
        $this->analyticsService
            ->method('getCourseRankings')
            ->willReturn([])
        ;
    }

    private function setupMinimalStatisticsMocks(): void
    {
        $this->courseRepository
            ->method('getStatistics')
            ->willReturn([])
        ;

        $this->collectRepository
            ->method('getCollectStatistics')
            ->willReturn(['total' => 0, 'by_course' => []])
        ;

        $this->evaluateRepository
            ->method('getEvaluateStatistics')
            ->willReturn(['total' => 0, 'by_course' => []])
        ;

        $this->auditRepository
            ->method('getAuditStatistics')
            ->willReturn([])
        ;
    }
}
