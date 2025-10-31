<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseContentFactory;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseContentImporter;

/**
 * @internal
 */
#[CoversClass(CourseContentImporter::class)]
final class CourseContentImporterTest extends TestCase
{
    private CourseContentImporter $importer;

    private CourseContentFactory $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(CourseContentFactory::class);
        $this->importer = new CourseContentImporter($this->factory);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseContentImporter::class, $this->importer);
    }

    public function testBatchImportContentWithEmptyData(): void
    {
        $course = new Course();
        $contentData = [];

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('chapters', $result);
        $this->assertArrayHasKey('lessons', $result);
        $this->assertArrayHasKey('outlines', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEmpty($result['errors']);
    }

    public function testBatchImportContentWithChapters(): void
    {
        $course = new Course();
        $contentData = [
            'chapters' => [
                ['title' => '章节1', 'sort_number' => 1],
                ['title' => '章节2', 'sort_number' => 2],
            ],
        ];

        $mockChapter = $this->createMockChapter();

        $this->factory->expects($this->exactly(2))
            ->method('createChapter')
            ->willReturn($mockChapter)
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['chapters']);
        $this->assertCount(2, $result['chapters']);
    }

    public function testBatchImportContentWithLessons(): void
    {
        $course = new Course();
        $contentData = [
            'chapters' => [
                [
                    'title' => '章节1',
                    'lessons' => [
                        ['title' => '课时1', 'duration_second' => 3600],
                        ['title' => '课时2', 'duration_second' => 1800],
                    ],
                ],
            ],
        ];

        $mockChapter = $this->createMockChapter();
        $mockLesson = $this->createMockLesson();

        $this->factory->method('createChapter')
            ->willReturn($mockChapter)
        ;

        $this->factory->expects($this->exactly(2))
            ->method('createLesson')
            ->willReturn($mockLesson)
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['lessons']);
        $this->assertCount(2, $result['lessons']);
    }

    public function testBatchImportContentWithOutlines(): void
    {
        $course = new Course();
        $contentData = [
            'outlines' => [
                ['title' => '大纲1', 'sort_number' => 1],
                ['title' => '大纲2', 'sort_number' => 2],
            ],
        ];

        $mockOutline = $this->createMockOutline();

        $this->factory->expects($this->exactly(2))
            ->method('createOutline')
            ->willReturn($mockOutline)
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['outlines']);
        $this->assertCount(2, $result['outlines']);
    }

    public function testBatchImportContentHandlesException(): void
    {
        $course = new Course();
        $contentData = [
            'chapters' => [
                ['title' => '章节1'],
            ],
        ];

        $this->factory->method('createChapter')
            ->willThrowException(new \Exception('Import error'))
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['errors']);
        $this->assertNotEmpty($result['errors']);
        $this->assertIsArray($result['errors']);
        $errorMessage = $result['errors'][0];
        $this->assertIsString($errorMessage);
        $this->assertStringContainsString('章节导入失败', $errorMessage);
    }

    public function testBatchImportContentSkipsInvalidChapterData(): void
    {
        $course = new Course();
        $contentData = [
            'chapters' => [
                'invalid_data',
                ['title' => '有效章节'],
            ],
        ];

        $mockChapter = $this->createMockChapter();

        $this->factory->expects($this->once())
            ->method('createChapter')
            ->willReturn($mockChapter)
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['chapters']);
        $this->assertCount(1, $result['chapters']);
    }

    public function testBatchImportContentSkipsInvalidLessonData(): void
    {
        $course = new Course();
        $contentData = [
            'chapters' => [
                [
                    'title' => '章节1',
                    'lessons' => [
                        'invalid_lesson',
                        ['title' => '有效课时'],
                    ],
                ],
            ],
        ];

        $mockChapter = $this->createMockChapter();
        $mockLesson = $this->createMockLesson();

        $this->factory->method('createChapter')
            ->willReturn($mockChapter)
        ;

        $this->factory->expects($this->once())
            ->method('createLesson')
            ->willReturn($mockLesson)
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['lessons']);
        $this->assertCount(1, $result['lessons']);
    }

    public function testBatchImportContentSkipsInvalidOutlineData(): void
    {
        $course = new Course();
        $contentData = [
            'outlines' => [
                'invalid_outline',
                ['title' => '有效大纲'],
            ],
        ];

        $mockOutline = $this->createMockOutline();

        $this->factory->expects($this->once())
            ->method('createOutline')
            ->willReturn($mockOutline)
        ;

        $result = $this->importer->batchImportContent($course, $contentData);

        $this->assertIsArray($result['outlines']);
        $this->assertCount(1, $result['outlines']);
    }

    /**
     * 创建Mock章节
     */
    private function createMockChapter(): Chapter
    {
        return new Chapter();
    }

    /**
     * 创建Mock课时
     */
    private function createMockLesson(): Lesson
    {
        return new Lesson();
    }

    /**
     * 创建Mock大纲
     */
    private function createMockOutline(): CourseOutline
    {
        return new CourseOutline();
    }
}
