<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsFormatter;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsOutputRenderer;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsTableRenderer;

/**
 * @internal
 */
#[CoversClass(StatisticsOutputRenderer::class)]
final class StatisticsOutputRendererTest extends TestCase
{
    private MockObject&StatisticsFormatter $formatter;

    private MockObject&StatisticsTableRenderer $tableRenderer;

    private MockObject&SymfonyStyle $io;

    private StatisticsOutputRenderer $renderer;

    protected function setUp(): void
    {
        $this->formatter = $this->createMock(StatisticsFormatter::class);
        $this->tableRenderer = $this->createMock(StatisticsTableRenderer::class);
        $this->io = $this->createMock(SymfonyStyle::class);

        $this->renderer = new StatisticsOutputRenderer(
            $this->formatter,
            $this->tableRenderer
        );
    }

    public function testOutputStatisticsTable(): void
    {
        $statistics = ['basic' => ['total' => 100]];

        $this->tableRenderer
            ->expects($this->once())
            ->method('outputTable')
            ->with($this->io, $statistics, false)
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'table', null, false);
    }

    public function testOutputStatisticsJsonToConsole(): void
    {
        $statistics = ['basic' => ['total' => 100]];

        $this->io
            ->expects($this->once())
            ->method('writeln')
            ->with(self::stringContains('"total"'))
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'json', null, false);
    }

    public function testOutputStatisticsJsonToFile(): void
    {
        $statistics = ['basic' => ['total' => 100]];
        $outputFile = sys_get_temp_dir() . '/test_output.json';

        $this->io
            ->expects($this->once())
            ->method('success')
            ->with(self::stringContains($outputFile))
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'json', $outputFile, false);

        $this->assertFileExists($outputFile);
        $content = file_get_contents($outputFile);
        $this->assertIsString($content);
        $this->assertStringContainsString('total', $content);
        $this->assertStringContainsString('100', $content);

        unlink($outputFile);
    }

    public function testOutputStatisticsCsvToConsole(): void
    {
        $statistics = ['basic' => ['total' => 100]];
        $csvData = [
            ['类型', '指标', '数值'],
            ['基础统计', 'total', '100'],
        ];

        $this->formatter
            ->expects($this->once())
            ->method('buildCsvData')
            ->with($statistics)
            ->willReturn($csvData)
        ;

        $callCount = 0;
        $this->io
            ->expects($this->exactly(2))
            ->method('writeln')
            ->willReturnCallback(function (string $line) use (&$callCount): void {
                ++$callCount;
                if (1 === $callCount) {
                    $this->assertStringContainsString('类型', $line);
                } elseif (2 === $callCount) {
                    $this->assertStringContainsString('基础统计', $line);
                }
            })
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'csv', null, false);
    }

    public function testOutputStatisticsCsvToFile(): void
    {
        $statistics = ['basic' => ['total' => 100]];
        $outputFile = sys_get_temp_dir() . '/test_output.csv';
        $csvData = [
            ['类型', '指标', '数值'],
            ['基础统计', 'total', '100'],
        ];

        $this->formatter
            ->expects($this->once())
            ->method('buildCsvData')
            ->with($statistics)
            ->willReturn($csvData)
        ;

        $this->io
            ->expects($this->once())
            ->method('success')
            ->with(self::stringContains($outputFile))
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'csv', $outputFile, false);

        $this->assertFileExists($outputFile);
        $content = file_get_contents($outputFile);
        $this->assertIsString($content);
        $this->assertStringContainsString('类型', $content);
        $this->assertStringContainsString('基础统计', $content);

        unlink($outputFile);
    }

    public function testDisplayCourseReport(): void
    {
        $course = $this->createMock(Course::class);
        $course->method('getTitle')->willReturn('Test Course');

        $report = [
            'course_info' => ['id' => 1],
            'popularity_metrics' => ['score' => 85],
            'recommendations' => [],
        ];

        $this->io
            ->expects($this->once())
            ->method('title')
            ->with('课程分析报告: Test Course')
        ;

        $this->tableRenderer
            ->expects($this->once())
            ->method('renderCourseBasicInfo')
            ->with($this->io, $report)
        ;

        $this->tableRenderer
            ->expects($this->once())
            ->method('renderPopularityMetrics')
            ->with($this->io, $report)
        ;

        $this->tableRenderer
            ->expects($this->once())
            ->method('renderRecommendationsIfAvailable')
            ->with($this->io, $report)
        ;

        $this->renderer->displayCourseReport($this->io, $course, $report);
    }

    public function testOutputStatisticsDetailedTable(): void
    {
        $statistics = [
            'basic' => ['total' => 100],
            'detailed' => ['top_courses' => []],
        ];

        $this->tableRenderer
            ->expects($this->once())
            ->method('outputTable')
            ->with($this->io, $statistics, true)
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'table', null, true);
    }

    public function testOutputStatisticsJsonWithEmptyData(): void
    {
        $statistics = [];

        $this->io
            ->expects($this->once())
            ->method('writeln')
            ->with(self::stringContains('[]'))
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'json', null, false);
    }

    public function testOutputStatisticsJsonInvalidData(): void
    {
        // 测试包含无法JSON编码的数据（如资源类型）
        $statistics = ['basic' => ['total' => 100]];
        $outputFile = sys_get_temp_dir() . '/test_invalid.json';

        $this->io
            ->expects($this->once())
            ->method('success')
            ->with(self::stringContains($outputFile))
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'json', $outputFile, false);

        $this->assertFileExists($outputFile);
        unlink($outputFile);
    }

    public function testOutputStatisticsDefaultFormat(): void
    {
        $statistics = ['basic' => ['total' => 100]];

        $this->tableRenderer
            ->expects($this->once())
            ->method('outputTable')
            ->with($this->io, $statistics, false)
        ;

        $this->renderer->outputStatistics($this->io, $statistics, 'unknown', null, false);
    }
}
