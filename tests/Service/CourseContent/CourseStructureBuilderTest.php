<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseStatisticsCalculator;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseStructureBuilder;

/**
 * @internal
 */
#[CoversClass(CourseStructureBuilder::class)]
final class CourseStructureBuilderTest extends TestCase
{
    private CourseStructureBuilder $builder;

    private ChapterRepository $chapterRepository;

    private CourseOutlineRepository $outlineRepository;

    private CourseStatisticsCalculator $statisticsCalculator;

    protected function setUp(): void
    {
        $this->chapterRepository = $this->createMock(ChapterRepository::class);
        $this->outlineRepository = $this->createMock(CourseOutlineRepository::class);
        $this->statisticsCalculator = $this->createMock(CourseStatisticsCalculator::class);

        $this->builder = new CourseStructureBuilder(
            $this->chapterRepository,
            $this->outlineRepository,
            $this->statisticsCalculator
        );
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseStructureBuilder::class, $this->builder);
    }

    public function testBuildCourseContentStructureReturnsArray(): void
    {
        $course = $this->createBasicCourse();

        $this->chapterRepository->method('findByCourseWithLessons')
            ->willReturn([])
        ;

        $this->outlineRepository->method('findPublishedByCourse')
            ->willReturn([])
        ;

        $this->statisticsCalculator->method('getCourseContentStatistics')
            ->willReturn([])
        ;

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course', $result);
        $this->assertArrayHasKey('chapters', $result);
        $this->assertArrayHasKey('outlines', $result);
        $this->assertArrayHasKey('statistics', $result);
    }

    public function testBuildCourseContentStructureIncludesCourseInfo(): void
    {
        $course = $this->createBasicCourse();

        $this->chapterRepository->method('findByCourseWithLessons')
            ->willReturn([])
        ;

        $this->outlineRepository->method('findPublishedByCourse')
            ->willReturn([])
        ;

        $this->statisticsCalculator->method('getCourseContentStatistics')
            ->willReturn([])
        ;

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result['course']);
        $this->assertArrayHasKey('id', $result['course']);
        $this->assertArrayHasKey('title', $result['course']);
        $this->assertArrayHasKey('description', $result['course']);
    }

    public function testBuildCourseContentStructureIncludesChapters(): void
    {
        $course = $this->createBasicCourse();
        $chapters = [$this->createMockChapter()];

        $this->chapterRepository->method('findByCourseWithLessons')
            ->willReturn($chapters)
        ;

        $this->outlineRepository->method('findPublishedByCourse')
            ->willReturn([])
        ;

        $this->statisticsCalculator->method('getCourseContentStatistics')
            ->willReturn([])
        ;

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result['chapters']);
        $this->assertCount(1, $result['chapters']);
    }

    public function testBuildCourseContentStructureIncludesOutlines(): void
    {
        $course = $this->createBasicCourse();
        $outlines = [$this->createMockOutline()];

        $this->chapterRepository->method('findByCourseWithLessons')
            ->willReturn([])
        ;

        $this->outlineRepository->method('findPublishedByCourse')
            ->willReturn($outlines)
        ;

        $this->statisticsCalculator->method('getCourseContentStatistics')
            ->willReturn([])
        ;

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result['outlines']);
        $this->assertCount(1, $result['outlines']);
    }

    public function testBuildStatisticsReturnsArray(): void
    {
        $course = $this->createCourseWithContent();

        $result = $this->builder->buildStatistics($course);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course_id', $result);
        $this->assertArrayHasKey('course_title', $result);
        $this->assertArrayHasKey('total_chapters', $result);
        $this->assertArrayHasKey('total_lessons', $result);
        $this->assertArrayHasKey('total_outlines', $result);
        $this->assertArrayHasKey('total_duration_seconds', $result);
    }

    public function testBuildStatisticsCalculatesTotals(): void
    {
        $course = $this->createCourseWithContent();

        $result = $this->builder->buildStatistics($course);

        $this->assertIsInt($result['total_chapters']);
        $this->assertIsInt($result['total_lessons']);
        $this->assertIsInt($result['total_outlines']);
        $this->assertIsInt($result['total_duration_seconds']);
    }

    /**
     * 创建基本课程
     */
    private function createBasicCourse(): Course
    {
        $course = new Course();
        $course->setTitle('测试课程');
        $course->setDescription('课程描述');

        return $course;
    }

    /**
     * 创建带有内容的课程
     */
    private function createCourseWithContent(): Course
    {
        $course = new Course();
        $course->setTitle('完整课程');

        return $course;
    }

    /**
     * 创建Mock章节
     */
    private function createMockChapter(): object
    {
        return new class {
            public function getId(): int
            {
                return 1;
            }

            public function getTitle(): string
            {
                return '章节1';
            }

            public function getSortNumber(): int
            {
                return 1;
            }

            /**
             * @return ArrayCollection<int, object>
             */
            public function getLessons(): ArrayCollection
            {
                $lesson = new class {
                    public function getId(): int
                    {
                        return 1;
                    }

                    public function getTitle(): string
                    {
                        return '课时1';
                    }

                    /**
                     * @phpstan-ignore return.unusedType
                     */
                    public function getVideoUrl(): ?string
                    {
                        return 'https://example.com/video.mp4';
                    }

                    public function getDurationSecond(): int
                    {
                        return 3600;
                    }

                    public function getSortNumber(): int
                    {
                        return 1;
                    }
                };

                /** @var ArrayCollection<int, object> */
                return new ArrayCollection([$lesson]);
            }
        };
    }

    /**
     * 创建Mock大纲
     */
    private function createMockOutline(): object
    {
        return new class {
            public function getId(): int
            {
                return 1;
            }

            public function getTitle(): string
            {
                return '大纲1';
            }

            /**
             * @phpstan-ignore return.unusedType
             */
            public function getLearningObjectives(): ?string
            {
                return '学习目标';
            }

            /**
             * @phpstan-ignore return.unusedType
             */
            public function getContentPoints(): ?string
            {
                return '内容要点';
            }

            /**
             * @phpstan-ignore return.unusedType
             */
            public function getKeyDifficulties(): ?string
            {
                return '重难点';
            }

            /**
             * @phpstan-ignore return.unusedType
             */
            public function getAssessmentCriteria(): ?string
            {
                return '考核标准';
            }

            /**
             * @phpstan-ignore return.unusedType
             */
            public function getEstimatedMinutes(): ?int
            {
                return 60;
            }

            public function getSortNumber(): int
            {
                return 1;
            }
        };
    }
}
