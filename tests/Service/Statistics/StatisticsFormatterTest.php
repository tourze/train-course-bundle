<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsFormatter;

/**
 * @internal
 */
#[CoversClass(StatisticsFormatter::class)]
final class StatisticsFormatterTest extends TestCase
{
    private StatisticsFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new StatisticsFormatter();
    }

    #[DataProvider('bytesProvider')]
    public function testFormatBytes(int $bytes, string $expected): void
    {
        $result = $this->formatter->formatBytes($bytes);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function bytesProvider(): array
    {
        return [
            'zero bytes' => [0, '0 B'],
            'small bytes' => [500, '500 B'],
            'one KB' => [1024, '1 KB'],
            'one MB' => [1048576, '1 MB'],
            'one GB' => [1073741824, '1 GB'],
            'one TB' => [1099511627776, '1 TB'],
            'fractional KB' => [1536, '1.5 KB'],
            'fractional MB' => [1572864, '1.5 MB'],
            'negative handled as zero' => [-100, '0 B'],
        ];
    }

    #[DataProvider('labelProvider')]
    public function testFormatLabel(string $key, string $expected): void
    {
        $result = $this->formatter->formatLabel($key);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function labelProvider(): array
    {
        return [
            'simple lowercase' => ['test', 'Test'],
            'with underscore' => ['total_courses', 'Total courses'],
            'multiple underscores' => ['average_collects_per_course', 'Average collects per course'],
            'already capitalized' => ['Test', 'Test'],
            'mixed case' => ['testCase', 'TestCase'],
        ];
    }

    #[DataProvider('metricValueProvider')]
    public function testFormatMetricValue(mixed $value, string $expected): void
    {
        $result = $this->formatter->formatMetricValue($value);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function metricValueProvider(): array
    {
        return [
            'integer' => [42, '42'],
            'float' => [3.14, '3.14'],
            'string' => ['test', 'test'],
            'true' => [true, '1'],
            'false' => [false, ''],
            'empty array' => [[], '[]'],
            'simple array' => [['a', 'b'], '["a","b"]'],
            'associative array' => [['key' => 'value'], '{"key":"value"}'],
            'null' => [null, ''],
            'zero' => [0, '0'],
            'empty string' => ['', ''],
        ];
    }

    public function testBuildCsvDataBasic(): void
    {
        $data = [
            'basic' => [
                'total_courses' => 100,
                'valid_courses' => 85,
                'invalid_courses' => 15,
            ],
            'courses' => [
                'by_status' => [
                    'active' => 85,
                    'inactive' => 15,
                ],
            ],
        ];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(6, $result); // header + 3 basic + 2 courses
        $this->assertSame(['类型', '指标', '数值'], $result[0]);
        $this->assertSame(['基础统计', 'total_courses', '100'], $result[1]);
        $this->assertSame(['基础统计', 'valid_courses', '85'], $result[2]);
        $this->assertSame(['基础统计', 'invalid_courses', '15'], $result[3]);
        $this->assertSame(['课程统计', 'active', '85'], $result[4]);
        $this->assertSame(['课程统计', 'inactive', '15'], $result[5]);
    }

    public function testBuildCsvDataWithMissingBasic(): void
    {
        $data = [
            'courses' => [
                'by_status' => [
                    'active' => 10,
                ],
            ],
        ];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(2, $result); // header + 1 course
        $this->assertSame(['类型', '指标', '数值'], $result[0]);
    }

    public function testBuildCsvDataWithMissingCourses(): void
    {
        $data = [
            'basic' => [
                'total_courses' => 50,
            ],
        ];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(2, $result); // header + 1 basic
        $this->assertSame(['类型', '指标', '数值'], $result[0]);
        $this->assertSame(['基础统计', 'total_courses', '50'], $result[1]);
    }

    public function testBuildCsvDataWithInvalidBasic(): void
    {
        $data = [
            'basic' => 'not_an_array',
            'courses' => [
                'by_status' => [
                    'active' => 5,
                ],
            ],
        ];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(2, $result); // header + 1 course (basic skipped)
    }

    public function testBuildCsvDataWithInvalidCourses(): void
    {
        $data = [
            'basic' => [
                'total' => 10,
            ],
            'courses' => 'not_an_array',
        ];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(2, $result); // header + 1 basic (courses skipped)
    }

    public function testBuildCsvDataEmpty(): void
    {
        $data = [];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(1, $result); // only header
        $this->assertSame(['类型', '指标', '数值'], $result[0]);
    }

    public function testBuildCsvDataWithNonScalarValues(): void
    {
        $data = [
            'basic' => [
                'array_value' => ['nested' => 'value'],
                'object_value' => (object) ['key' => 'value'],
                'null_value' => null,
            ],
        ];

        $result = $this->formatter->buildCsvData($data);

        // Method signature guarantees array return
        $this->assertCount(4, $result); // header + 3 rows
        $this->assertSame(['基础统计', 'array_value', ''], $result[1]);
        $this->assertSame(['基础统计', 'object_value', ''], $result[2]);
        $this->assertSame(['基础统计', 'null_value', ''], $result[3]);
    }
}
