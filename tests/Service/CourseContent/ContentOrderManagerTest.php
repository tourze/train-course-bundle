<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Repository\LessonRepository;
use Tourze\TrainCourseBundle\Service\CourseContent\ContentOrderManager;

/**
 * @internal
 */
#[CoversClass(ContentOrderManager::class)]
final class ContentOrderManagerTest extends TestCase
{
    private ContentOrderManager $manager;

    private ChapterRepository $chapterRepository;

    private LessonRepository $lessonRepository;

    protected function setUp(): void
    {
        $this->chapterRepository = $this->createMock(ChapterRepository::class);
        $this->lessonRepository = $this->createMock(LessonRepository::class);
        $this->manager = new ContentOrderManager(
            $this->chapterRepository,
            $this->lessonRepository
        );
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ContentOrderManager::class, $this->manager);
    }

    public function testReorderChaptersWithEmptyArray(): void
    {
        $course = new Course();
        $chapterIds = [];

        $result = $this->manager->reorderChapters($course, $chapterIds);

        $this->assertTrue($result);
    }

    public function testReorderChaptersWithValidIds(): void
    {
        $course = new Course();
        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter2 = new Chapter();
        $chapter2->setCourse($course);

        $chapterIds = ['1', '2'];

        $this->chapterRepository->expects($this->exactly(2))
            ->method('find')
            ->willReturnOnConsecutiveCalls($chapter1, $chapter2)
        ;

        $result = $this->manager->reorderChapters($course, $chapterIds);

        $this->assertTrue($result);
    }

    public function testReorderChaptersHandlesException(): void
    {
        $course = new Course();
        $chapterIds = ['1'];

        $this->chapterRepository->method('find')
            ->willThrowException(new \Exception('Repository error'))
        ;

        $result = $this->manager->reorderChapters($course, $chapterIds);

        $this->assertFalse($result);
    }

    public function testReorderLessonsWithEmptyArray(): void
    {
        $chapter = new Chapter();
        $lessonIds = [];

        $result = $this->manager->reorderLessons($chapter, $lessonIds);

        $this->assertTrue($result);
    }

    public function testReorderLessonsWithValidIds(): void
    {
        $chapter = new Chapter();
        $lesson1 = new Lesson();
        $lesson1->setChapter($chapter);
        $lesson2 = new Lesson();
        $lesson2->setChapter($chapter);

        $lessonIds = ['1', '2'];

        $this->lessonRepository->expects($this->exactly(2))
            ->method('find')
            ->willReturnOnConsecutiveCalls($lesson1, $lesson2)
        ;

        $result = $this->manager->reorderLessons($chapter, $lessonIds);

        $this->assertTrue($result);
    }

    public function testReorderLessonsHandlesException(): void
    {
        $chapter = new Chapter();
        $lessonIds = ['1'];

        $this->lessonRepository->method('find')
            ->willThrowException(new \Exception('Repository error'))
        ;

        $result = $this->manager->reorderLessons($chapter, $lessonIds);

        $this->assertFalse($result);
    }

    public function testReorderChaptersSkipsInvalidChapters(): void
    {
        $course = new Course();
        $chapterIds = ['1', '2'];

        $this->chapterRepository->method('find')
            ->willReturn(null)
        ;

        $result = $this->manager->reorderChapters($course, $chapterIds);

        $this->assertTrue($result);
    }

    public function testReorderLessonsSkipsInvalidLessons(): void
    {
        $chapter = new Chapter();
        $lessonIds = ['1', '2'];

        $this->lessonRepository->method('find')
            ->willReturn(null)
        ;

        $result = $this->manager->reorderLessons($chapter, $lessonIds);

        $this->assertTrue($result);
    }
}
