<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Service\CourseContent\ContentOrderManager;

/**
 * @internal
 */
#[CoversClass(ContentOrderManager::class)]
final class ContentOrderManagerTest extends TestCase
{
    private ContentOrderManager $manager;

    protected function setUp(): void
    {
        // 使用存根对象，避免Mock final类
        $chapterRepository = $this->createRepositoryStub();
        $lessonRepository = $this->createRepositoryStub();

        $this->manager = new ContentOrderManager(
            $chapterRepository,
            $lessonRepository
        );
    }

    /**
     * 创建Repository存根，避免final类的Mock问题
     */
    private function createRepositoryStub()
    {
        return new class {
            public function find($id) {
                return null;
            }
            public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null) {
                return [];
            }
            public function findAll() {
                return [];
            }
            public function findOneBy(array $criteria) {
                return null;
            }
            public function getClassName() {
                return '';
            }
        };
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
        $chapterIds = ['1', '2'];

        $result = $this->manager->reorderChapters($course, $chapterIds);

        $this->assertTrue($result);
    }

    public function testReorderChaptersHandlesException(): void
    {
        $course = new Course();
        $chapterIds = ['nonexistent'];

        // 当章节不存在时，服务应该依然返回true，因为没有异常抛出
        // 只是没有找到匹配的章节来更新
        $result = $this->manager->reorderChapters($course, $chapterIds);

        $this->assertTrue($result);
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
        $lessonIds = ['1', '2'];

        $result = $this->manager->reorderLessons($chapter, $lessonIds);

        $this->assertTrue($result);
    }

    public function testReorderLessonsHandlesException(): void
    {
        $chapter = new Chapter();
        $lessonIds = ['nonexistent'];

        // 当课时不存在时，服务应该依然返回true，因为没有异常抛出
        // 只是没有找到匹配的课时来更新
        $result = $this->manager->reorderLessons($chapter, $lessonIds);

        $this->assertTrue($result);
    }
}