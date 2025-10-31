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

/**
 * @internal
 */
#[CoversClass(CourseContentFactory::class)]
final class CourseContentFactoryTest extends TestCase
{
    private CourseContentFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CourseContentFactory();
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseContentFactory::class, $this->factory);
    }

    public function testCreateChapterWithCompleteData(): void
    {
        $course = new Course();
        $data = [
            'title' => '章节标题',
            'sort_number' => 1,
        ];

        $chapter = $this->factory->createChapter($course, $data);

        $this->assertInstanceOf(Chapter::class, $chapter);
        $this->assertSame('章节标题', $chapter->getTitle());
        $this->assertSame(1, $chapter->getSortNumber());
        $this->assertSame($course, $chapter->getCourse());
    }

    public function testCreateChapterWithMinimalData(): void
    {
        $course = new Course();
        $data = [];

        $chapter = $this->factory->createChapter($course, $data);

        $this->assertInstanceOf(Chapter::class, $chapter);
        $this->assertSame('', $chapter->getTitle());
        $this->assertSame(0, $chapter->getSortNumber());
    }

    public function testCreateChapterWithInvalidDataTypes(): void
    {
        $course = new Course();
        $data = [
            'title' => 123,
            'sort_number' => 'invalid',
        ];

        $chapter = $this->factory->createChapter($course, $data);

        $this->assertInstanceOf(Chapter::class, $chapter);
        $this->assertSame('', $chapter->getTitle());
        $this->assertSame(0, $chapter->getSortNumber());
    }

    public function testCreateLessonWithCompleteData(): void
    {
        $chapter = new Chapter();
        $data = [
            'title' => '课时标题',
            'video_url' => 'https://example.com/video.mp4',
            'duration_second' => 3600,
            'sort_number' => 1,
        ];

        $lesson = $this->factory->createLesson($chapter, $data);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertSame('课时标题', $lesson->getTitle());
        $this->assertSame('https://example.com/video.mp4', $lesson->getVideoUrl());
        $this->assertSame(3600, $lesson->getDurationSecond());
        $this->assertSame(1, $lesson->getSortNumber());
        $this->assertSame($chapter, $lesson->getChapter());
    }

    public function testCreateLessonWithMinimalData(): void
    {
        $chapter = new Chapter();
        $data = [];

        $lesson = $this->factory->createLesson($chapter, $data);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertSame('', $lesson->getTitle());
        $this->assertNull($lesson->getVideoUrl());
        $this->assertSame(0, $lesson->getDurationSecond());
        $this->assertSame(0, $lesson->getSortNumber());
    }

    public function testCreateLessonWithInvalidDataTypes(): void
    {
        $chapter = new Chapter();
        $data = [
            'title' => 123,
            'video_url' => 123,
            'duration_second' => 'invalid',
            'sort_number' => 'invalid',
        ];

        $lesson = $this->factory->createLesson($chapter, $data);

        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertSame('', $lesson->getTitle());
        $this->assertNull($lesson->getVideoUrl());
        $this->assertSame(0, $lesson->getDurationSecond());
        $this->assertSame(0, $lesson->getSortNumber());
    }

    public function testCreateOutlineWithCompleteData(): void
    {
        $course = new Course();
        $data = [
            'title' => '大纲标题',
            'learning_objectives' => '学习目标',
            'content_points' => '内容要点',
            'key_difficulties' => '重难点',
            'assessment_criteria' => '考核标准',
            'references' => '参考资料',
            'estimated_minutes' => 60,
            'sort_number' => 1,
            'status' => 'published',
        ];

        $outline = $this->factory->createOutline($course, $data);

        $this->assertInstanceOf(CourseOutline::class, $outline);
        $this->assertSame('大纲标题', $outline->getTitle());
        $this->assertSame('学习目标', $outline->getLearningObjectives());
        $this->assertSame('内容要点', $outline->getContentPoints());
        $this->assertSame('重难点', $outline->getKeyDifficulties());
        $this->assertSame('考核标准', $outline->getAssessmentCriteria());
        $this->assertSame('参考资料', $outline->getReferences());
        $this->assertSame(60, $outline->getEstimatedMinutes());
        $this->assertSame(1, $outline->getSortNumber());
        $this->assertSame('published', $outline->getStatus());
        $this->assertSame($course, $outline->getCourse());
    }

    public function testCreateOutlineWithMinimalData(): void
    {
        $course = new Course();
        $data = [];

        $outline = $this->factory->createOutline($course, $data);

        $this->assertInstanceOf(CourseOutline::class, $outline);
        $this->assertSame('', $outline->getTitle());
        $this->assertNull($outline->getLearningObjectives());
        $this->assertNull($outline->getContentPoints());
        $this->assertNull($outline->getKeyDifficulties());
        $this->assertNull($outline->getAssessmentCriteria());
        $this->assertNull($outline->getReferences());
        $this->assertNull($outline->getEstimatedMinutes());
        $this->assertSame(0, $outline->getSortNumber());
        $this->assertSame('draft', $outline->getStatus());
    }

    public function testCreateOutlineWithInvalidDataTypes(): void
    {
        $course = new Course();
        $data = [
            'title' => 123,
            'learning_objectives' => 123,
            'estimated_minutes' => 'invalid',
            'sort_number' => 'invalid',
        ];

        $outline = $this->factory->createOutline($course, $data);

        $this->assertInstanceOf(CourseOutline::class, $outline);
        $this->assertSame('', $outline->getTitle());
        $this->assertNull($outline->getLearningObjectives());
        $this->assertNull($outline->getEstimatedMinutes());
        $this->assertSame(0, $outline->getSortNumber());
    }
}
