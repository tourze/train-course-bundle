<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsFormatter;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsTableRenderer;

/**
 * @internal
 */
#[CoversClass(StatisticsTableRenderer::class)]
final class StatisticsTableRendererTest extends TestCase
{
    private MockObject&StatisticsFormatter $formatter;

    private MockObject&SymfonyStyle $io;

    private StatisticsTableRenderer $renderer;

    protected function setUp(): void
    {
        $this->formatter = $this->createMock(StatisticsFormatter::class);
        $this->io = $this->createMock(SymfonyStyle::class);

        $this->renderer = new StatisticsTableRenderer($this->formatter);
    }

    public function testOutputTableBasic(): void
    {
        $statistics = [
            'basic' => [
                'total_courses' => 100,
                'valid_courses' => 85,
            ],
            'courses' => [
                'by_status' => [
                    'active' => 85,
                    'inactive' => 15,
                ],
            ],
        ];

        $this->io
            ->expects($this->exactly(2))
            ->method('section')
            ->with(self::logicalOr('基础统计', '课程统计'))
        ;

        $this->formatter
            ->expects($this->exactly(2))
            ->method('formatLabel')
            ->willReturnCallback(fn (string $key): string => ucfirst((string) str_replace('_', ' ', $key)))
        ;

        $this->renderer->outputTable($this->io, $statistics, false);
    }

    public function testOutputTableDetailed(): void
    {
        $statistics = [
            'basic' => ['total' => 10],
            'courses' => ['by_status' => ['active' => 5]],
            'detailed' => [
                'top_courses' => [
                    [
                        'title' => 'Course 1',
                        'popularity_score' => 95,
                        'quality_score' => 88,
                        'collect_count' => 100,
                        'evaluate_count' => 50,
                        'average_rating' => 4.5,
                    ],
                ],
            ],
        ];

        $this->io
            ->expects($this->exactly(3))
            ->method('section')
            ->with(self::logicalOr('基础统计', '课程统计', '热门课程排行榜'))
        ;

        $this->renderer->outputTable($this->io, $statistics, true);
    }

    public function testRenderBasicStatisticsTable(): void
    {
        $statistics = [
            'basic' => [
                'total_courses' => 100,
                'valid_courses' => 85,
                'invalid_courses' => 15,
            ],
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('基础统计')
        ;

        $this->formatter
            ->expects($this->exactly(3))
            ->method('formatLabel')
            ->willReturnCallback(fn (string $key): string => ucfirst((string) str_replace('_', ' ', $key)))
        ;

        $this->renderer->renderBasicStatisticsTable($this->io, $statistics);
    }

    public function testRenderBasicStatisticsTableWithMissingData(): void
    {
        $statistics = [];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('基础统计')
        ;

        $this->formatter
            ->expects($this->never())
            ->method('formatLabel')
        ;

        $this->renderer->renderBasicStatisticsTable($this->io, $statistics);
    }

    public function testRenderCourseStatisticsTable(): void
    {
        $statistics = [
            'courses' => [
                'by_status' => [
                    'active' => 85,
                    'inactive' => 15,
                    'draft' => 5,
                ],
            ],
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('课程统计')
        ;

        $this->renderer->renderCourseStatisticsTable($this->io, $statistics);
    }

    public function testRenderCourseStatisticsTableWithInvalidData(): void
    {
        $statistics = [
            'courses' => [
                'by_status' => 'not_an_array',
            ],
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('课程统计')
        ;

        $this->renderer->renderCourseStatisticsTable($this->io, $statistics);
    }

    public function testRenderCourseBasicInfo(): void
    {
        $report = [
            'course_info' => [
                'id' => 1,
                'title' => 'Test Course',
                'status' => 'active',
            ],
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('基础信息')
        ;

        $this->formatter
            ->expects($this->exactly(3))
            ->method('formatLabel')
            ->willReturnCallback(fn (string $key): string => ucfirst($key))
        ;

        $this->renderer->renderCourseBasicInfo($this->io, $report);
    }

    public function testRenderPopularityMetrics(): void
    {
        $report = [
            'popularity_metrics' => [
                'popularity_score' => 95.5,
                'quality_score' => 88.0,
                'collect_count' => 100,
            ],
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('受欢迎程度指标')
        ;

        $this->formatter
            ->expects($this->exactly(3))
            ->method('formatLabel')
            ->willReturnCallback(fn (string $key): string => ucfirst($key))
        ;

        $this->formatter
            ->expects($this->exactly(3))
            ->method('formatMetricValue')
            ->willReturnCallback(function (mixed $value): string {
                if (is_scalar($value)) {
                    return (string) $value;
                }

                return '';
            })
        ;

        $this->renderer->renderPopularityMetrics($this->io, $report);
    }

    public function testRenderRecommendationsIfAvailable(): void
    {
        $report = [
            'recommendations' => [
                [
                    'priority' => 'high',
                    'message' => 'Improve course content',
                ],
                [
                    'priority' => 'medium',
                    'message' => 'Add more examples',
                ],
            ],
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('改进建议')
        ;

        $this->io
            ->expects($this->exactly(2))
            ->method('text')
            ->with(self::logicalOr(
                '[HIGH] Improve course content',
                '[MEDIUM] Add more examples'
            ))
        ;

        $this->renderer->renderRecommendationsIfAvailable($this->io, $report);
    }

    public function testRenderRecommendationsIfAvailableWithNoRecommendations(): void
    {
        $report = [
            'recommendations' => [],
        ];

        $this->io
            ->expects($this->never())
            ->method('section')
        ;

        $this->io
            ->expects($this->never())
            ->method('text')
        ;

        $this->renderer->renderRecommendationsIfAvailable($this->io, $report);
    }

    public function testRenderRecommendationsIfAvailableWithMissingData(): void
    {
        $report = [];

        $this->io
            ->expects($this->never())
            ->method('section')
        ;

        $this->renderer->renderRecommendationsIfAvailable($this->io, $report);
    }

    public function testRenderRecommendationsWithInvalidStructure(): void
    {
        $report = [
            'recommendations' => [
                ['priority' => 'high'], // missing message
                ['message' => 'test'], // missing priority
                'invalid_item', // not an array
            ],
        ];

        // Section is called because recommendations array exists and is not empty
        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('改进建议')
        ;

        // But text is never called because none of the items are valid
        $this->io
            ->expects($this->never())
            ->method('text')
        ;

        $this->renderer->renderRecommendationsIfAvailable($this->io, $report);
    }

    public function testOutputTableWithEmptyTopCourses(): void
    {
        $statistics = [
            'basic' => ['total' => 10],
            'courses' => ['by_status' => []],
            'detailed' => [
                'top_courses' => [],
            ],
        ];

        $this->io
            ->expects($this->exactly(2))
            ->method('section')
            ->with(self::logicalOr('基础统计', '课程统计'))
        ;

        $this->renderer->outputTable($this->io, $statistics, true);
    }

    public function testRenderBasicStatisticsTableWithNonArrayBasic(): void
    {
        $statistics = [
            'basic' => 'not_an_array',
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('基础统计')
        ;

        $this->formatter
            ->expects($this->never())
            ->method('formatLabel')
        ;

        $this->renderer->renderBasicStatisticsTable($this->io, $statistics);
    }

    public function testRenderPopularityMetricsWithInvalidData(): void
    {
        $report = [
            'popularity_metrics' => 'not_an_array',
        ];

        $this->io
            ->expects($this->once())
            ->method('section')
            ->with('受欢迎程度指标')
        ;

        $this->formatter
            ->expects($this->never())
            ->method('formatLabel')
        ;

        $this->renderer->renderPopularityMetrics($this->io, $report);
    }
}
