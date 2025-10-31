<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseStatisticsCalculator;

/**
 * @internal
 */
#[CoversClass(CourseStatisticsCalculator::class)]
final class CourseStatisticsCalculatorTest extends TestCase
{
    private CourseStatisticsCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CourseStatisticsCalculator();
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseStatisticsCalculator::class, $this->calculator);
    }

    public function testCalculateCourseStatisticsWithEmptyArray(): void
    {
        $courseData = [];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('chapter_count', $result);
        $this->assertArrayHasKey('lesson_count', $result);
        $this->assertSame(0, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
    }

    public function testCalculateCourseStatisticsWithSingleCourseNoChapters(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(0, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
    }

    public function testCalculateCourseStatisticsWithSingleCourseWithChapters(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    [
                        'id' => 1,
                        'title' => '第一章',
                        'lessons' => [
                            ['id' => 1, 'title' => '课时1'],
                            ['id' => 2, 'title' => '课时2'],
                        ],
                    ],
                    [
                        'id' => 2,
                        'title' => '第二章',
                        'lessons' => [
                            ['id' => 3, 'title' => '课时3'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(2, $result['chapter_count']);
        $this->assertSame(3, $result['lesson_count']);
    }

    public function testCalculateCourseStatisticsWithMultipleCourses(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    [
                        'id' => 1,
                        'title' => '第一章',
                        'lessons' => [
                            ['id' => 1, 'title' => '课时1'],
                            ['id' => 2, 'title' => '课时2'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => '课程2',
                'chapters' => [
                    [
                        'id' => 2,
                        'title' => '第一章',
                        'lessons' => [
                            ['id' => 3, 'title' => '课时3'],
                        ],
                    ],
                    [
                        'id' => 3,
                        'title' => '第二章',
                        'lessons' => [
                            ['id' => 4, 'title' => '课时4'],
                            ['id' => 5, 'title' => '课时5'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(3, $result['chapter_count']); // 1 + 2
        $this->assertSame(5, $result['lesson_count']); // 2 + 3
    }

    public function testCalculateCourseStatisticsWithChaptersButNoLessons(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    [
                        'id' => 1,
                        'title' => '第一章',
                        'lessons' => [],
                    ],
                    [
                        'id' => 2,
                        'title' => '第二章',
                        'lessons' => [],
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(2, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
    }

    public function testCalculateCourseStatisticsWithMissingChaptersKey(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                // chapters key missing
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(0, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
    }

    public function testCalculateCourseStatisticsWithNonCountableChapters(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => 'not-an-array',
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(0, $result['chapter_count']);
        $this->assertSame(0, $result['lesson_count']);
    }

    public function testCalculateCourseStatisticsWithMixedData(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    [
                        'id' => 1,
                        'title' => '第一章',
                        'lessons' => [
                            ['id' => 1, 'title' => '课时1'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => '课程2',
                // chapters key missing
            ],
            [
                'id' => 3,
                'title' => '课程3',
                'chapters' => [
                    [
                        'id' => 2,
                        'title' => '第一章',
                        'lessons' => [
                            ['id' => 2, 'title' => '课时2'],
                            ['id' => 3, 'title' => '课时3'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(2, $result['chapter_count']); // 课程1: 1章, 课程2: 0章, 课程3: 1章
        $this->assertSame(3, $result['lesson_count']); // 课程1: 1课时, 课程2: 0课时, 课程3: 2课时
    }

    public function testCalculateCourseStatisticsWithChapterMissingLessonsKey(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    [
                        'id' => 1,
                        'title' => '第一章',
                        // lessons key missing
                    ],
                    [
                        'id' => 2,
                        'title' => '第二章',
                        'lessons' => [
                            ['id' => 1, 'title' => '课时1'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(2, $result['chapter_count']);
        $this->assertSame(1, $result['lesson_count']); // 只有第二章有课时
    }

    public function testCalculateCourseStatisticsWithNonCountableLessons(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    [
                        'id' => 1,
                        'title' => '第一章',
                        'lessons' => 'not-an-array',
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(1, $result['chapter_count']); // 章节仍然计数
        $this->assertSame(0, $result['lesson_count']); // 课时不可计数
    }

    public function testCalculateCourseStatisticsWithLargeDataset(): void
    {
        $courseData = [];
        $expectedChapterCount = 0;
        $expectedLessonCount = 0;

        // 创建10个课程，每个课程5章，每章10个课时
        for ($i = 1; $i <= 10; ++$i) {
            $chapters = [];
            for ($j = 1; $j <= 5; ++$j) {
                $lessons = [];
                for ($k = 1; $k <= 10; ++$k) {
                    $lessons[] = ['id' => ($j * 10 + $k), 'title' => "课时{$k}"];
                    ++$expectedLessonCount;
                }
                $chapters[] = [
                    'id' => $j,
                    'title' => "第{$j}章",
                    'lessons' => $lessons,
                ];
                ++$expectedChapterCount;
            }
            $courseData[] = [
                'id' => $i,
                'title' => "课程{$i}",
                'chapters' => $chapters,
            ];
        }

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame($expectedChapterCount, $result['chapter_count']); // 10 * 5 = 50
        $this->assertSame($expectedLessonCount, $result['lesson_count']); // 10 * 5 * 10 = 500
    }

    public function testCalculateCourseStatisticsWithNonArrayChapter(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程1',
                'chapters' => [
                    'not-an-array-chapter',
                    [
                        'id' => 1,
                        'title' => '第一章',
                        'lessons' => [
                            ['id' => 1, 'title' => '课时1'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->calculator->calculateCourseStatistics($courseData);

        $this->assertSame(2, $result['chapter_count']); // 两个元素都被计数
        $this->assertSame(1, $result['lesson_count']); // 只有有效章节的课时被计数
    }
}
