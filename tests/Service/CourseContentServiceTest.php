<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Service\CourseContentService;
use Tourze\TrainCourseBundle\Tests\Factory\CourseFactory;

/**
 * CourseContentService 集成测试
 *
 * @internal
 */
#[CoversClass(CourseContentService::class)]
#[RunTestsInSeparateProcesses]
final class CourseContentServiceTest extends AbstractIntegrationTestCase
{
    private CourseContentService $service;
    private EntityManagerInterface $em;

    protected function onSetUp(): void
    {
        // 获取集成测试所需服务
        $this->service = self::getService(CourseContentService::class);
        $this->em = self::getService(EntityManagerInterface::class);
    }

    public function testServiceExists(): void
    {
        // 验证服务类可以实例化
        $reflection = new \ReflectionClass(CourseContentService::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testCreateChapter(): void
    {
        // 创建并持久化基础课程
        $course = $this->createPersistedCourse('测试课程');

        // 调用服务创建章节
        $chapter = $this->service->createChapter($course, [
            'title' => '第一章 基础知识',
            'sortNumber' => 1,
        ]);

        // 验证返回对象类型和属性
        $this->assertInstanceOf(Chapter::class, $chapter);
        $this->assertSame('第一章 基础知识', $chapter->getTitle());
        $this->assertSame($course->getId(), $chapter->getCourse()->getId());
        $this->assertSame(1, $chapter->getSortNumber());

        // 验证数据已持久化到数据库
        $this->em->clear();
        $reloadedChapter = $this->em->getRepository(Chapter::class)->find($chapter->getId());
        $this->assertNotNull($reloadedChapter);
        $this->assertSame('第一章 基础知识', $reloadedChapter->getTitle());
    }

    public function testCreateLesson(): void
    {
        // 创建完整的课程和章节
        $course = $this->createPersistedCourse('测试课程');
        $chapter = $this->service->createChapter($course, ['title' => '第一章', 'sortNumber' => 1]);

        // 调用服务创建课时
        $lesson = $this->service->createLesson($chapter, [
            'title' => '第1.1节 入门',
            'videoUrl' => 'https://example.com/video1.mp4',
            'durationSecond' => 1800,
            'sortNumber' => 1,
        ]);

        // 验证返回对象
        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertSame('第1.1节 入门', $lesson->getTitle());
        $this->assertSame('https://example.com/video1.mp4', $lesson->getVideoUrl());
        $this->assertSame(1800, $lesson->getDurationSecond());

        // 验证数据持久化
        $this->em->clear();
        $reloadedLesson = $this->em->getRepository(Lesson::class)->find($lesson->getId());
        $this->assertNotNull($reloadedLesson);
        $this->assertSame('第1.1节 入门', $reloadedLesson->getTitle());
    }

    public function testGetCourseContentStructure(): void
    {
        // 创建包含章节和课时的完整课程
        $course = $this->createPersistedCourseWithLessons(2, 3);

        // 获取课程内容结构
        $structure = $this->service->getCourseContentStructure($course);

        // 验证结构返回数组
        $this->assertIsArray($structure);

        // 验证结构包含期望的键
        $this->assertTrue(isset($structure['chapters']));
        $this->assertIsArray($structure['chapters']);

        // 验证章节数量
        $this->assertCount(2, $structure['chapters']);

        // 验证第一章包含课时
        $firstChapter = $structure['chapters'][0] ?? [];
        $this->assertIsArray($firstChapter);
        $this->assertArrayHasKey('lessons', $firstChapter);
        $lessons = $firstChapter['lessons'];
        $this->assertIsArray($lessons);
        $this->assertCount(3, $lessons);
    }

    public function testGetCourseContentStatistics(): void
    {
        // 创建包含内容的课程
        $course = $this->createPersistedCourseWithLessons(3, 2);

        // 获取统计信息
        $stats = $this->service->getCourseContentStatistics($course);

        // 验证统计结果为数组
        $this->assertIsArray($stats);

        // 验证统计数据的合理性
        if (isset($stats['total_chapters'])) {
            $this->assertIsInt($stats['total_chapters']);
            $this->assertGreaterThanOrEqual(0, $stats['total_chapters']);
        }

        if (isset($stats['total_lessons'])) {
            $this->assertIsInt($stats['total_lessons']);
            $this->assertGreaterThanOrEqual(0, $stats['total_lessons']);
        }
    }

    public function testReorderChapters(): void
    {
        // 创建多个章节的课程
        $course = $this->createPersistedCourse('测试课程');
        $chapter1 = $this->service->createChapter($course, ['title' => '第一章', 'sortNumber' => 1]);
        $chapter2 = $this->service->createChapter($course, ['title' => '第二章', 'sortNumber' => 2]);
        $chapter3 = $this->service->createChapter($course, ['title' => '第三章', 'sortNumber' => 3]);

        // 重新排序章节（新顺序：chapter2, chapter3, chapter1）
        $id1 = $chapter1->getId();
        $id2 = $chapter2->getId();
        $id3 = $chapter3->getId();

        // 方法期望 array<int, string> 格式，索引位置表示新顺序
        /** @var array<int, string> $newOrder */
        $newOrder = [];
        if (is_string($id2)) {
            $newOrder[] = $id2;
        }
        if (is_string($id3)) {
            $newOrder[] = $id3;
        }
        if (is_string($id1)) {
            $newOrder[] = $id1;
        }

        $this->service->reorderChapters($course, $newOrder);

        // 验证排序已更新
        $this->em->clear();
        $reloadedChapter1 = $this->em->getRepository(Chapter::class)->find($id1);
        $this->assertNotNull($reloadedChapter1);
        $this->assertSame(3, $reloadedChapter1->getSortNumber());
    }

    public function testClearCourseContentCache(): void
    {
        // 创建课程并获取结构（会被缓存）
        $course = $this->createPersistedCourseWithLessons(1, 2);
        $structure1 = $this->service->getCourseContentStructure($course);

        // 修改课程内容
        $this->service->createChapter($course, ['title' => '新章节', 'sortNumber' => 2]);

        // 再次获取结构，应该获得最新数据（缓存已被清除）
        $structure2 = $this->service->getCourseContentStructure($course);

        // 验证结构已更新
        $this->assertNotSame(json_encode($structure1), json_encode($structure2));
    }

    /**
     * 创建并持久化一个简单课程
     */
    private function createPersistedCourse(string $title): Course
    {
        $course = CourseFactory::create(['title' => $title]);
        $this->em->persist($course);
        $this->em->flush();

        return $course;
    }

    public function testCreateOutline(): void
    {
        // 创建基础课程
        $course = $this->createPersistedCourse('测试课程');

        // 调用服务创建大纲
        $outline = $this->service->createOutline($course, [
            'title' => '课程大纲',
            'description' => '这是一个测试大纲',
        ]);

        // 验证返回对象类型和属性
        $this->assertInstanceOf(CourseOutline::class, $outline);
        $this->assertSame('课程大纲', $outline->getTitle());
        $outlineCourse = $outline->getCourse();
        $this->assertNotNull($outlineCourse);
        $this->assertSame($course->getId(), $outlineCourse->getId());

        // 验证数据已持久化到数据库
        $this->em->clear();
        $reloadedOutline = $this->em->getRepository(CourseOutline::class)->find($outline->getId());
        $this->assertNotNull($reloadedOutline);
        $this->assertSame('课程大纲', $reloadedOutline->getTitle());
    }

    public function testBatchImportContent(): void
    {
        // 创建基础课程
        $course = $this->createPersistedCourse('批量导入测试课程');

        // 准备导入数据
        $contentData = [
            'chapters' => [
                [
                    'title' => '第一章',
                    'sortNumber' => 1,
                    'lessons' => [
                        [
                            'title' => '第1.1节',
                            'videoUrl' => 'https://example.com/video1.mp4',
                            'durationSecond' => 1800,
                            'sortNumber' => 1,
                        ],
                    ],
                ],
            ],
        ];

        // 调用批量导入服务
        $result = $this->service->batchImportContent($course, $contentData);

        // 验证返回结果
        $this->assertIsArray($result);
        $this->assertTrue(isset($result['chapters']));
        $this->assertIsArray($result['chapters']);
        $this->assertCount(1, $result['chapters']);

        // 验证数据已持久化
        $chapters = $this->em->getRepository(Chapter::class)->findBy(['course' => $course]);
        $this->assertCount(1, $chapters);
        $this->assertSame('第一章', $chapters[0]->getTitle());
    }

    public function testReorderLessons(): void
    {
        // 创建包含多个课时的章节
        $course = $this->createPersistedCourse('测试课程');
        $chapter = $this->service->createChapter($course, ['title' => '第一章', 'sortNumber' => 1]);

        $lesson1 = $this->service->createLesson($chapter, [
            'title' => '第1.1节',
            'videoUrl' => 'https://example.com/video1.mp4',
            'durationSecond' => 1800,
            'sortNumber' => 1,
        ]);

        $lesson2 = $this->service->createLesson($chapter, [
            'title' => '第1.2节',
            'videoUrl' => 'https://example.com/video2.mp4',
            'durationSecond' => 2000,
            'sortNumber' => 2,
        ]);

        // 重新排序课时
        $id1 = $lesson1->getId();
        $id2 = $lesson2->getId();

        $newOrder = [];
        if (is_string($id2)) {
            $newOrder[] = $id2;
        }
        if (is_string($id1)) {
            $newOrder[] = $id1;
        }

        // 调用重排序服务
        $result = $this->service->reorderLessons($chapter, $newOrder);

        // 验证重排序成功
        $this->assertTrue($result);

        // 验证排序已更新到数据库
        $this->em->clear();
        $reloadedChapter = $this->em->getRepository(Chapter::class)->find($chapter->getId());
        $this->assertNotNull($reloadedChapter);

        $lessons = $reloadedChapter->getLessons();
        $this->assertCount(2, $lessons);

        // 验证排序顺序（第一个应该是lesson2，第二个应该是lesson1）
        $lessonArray = $lessons->toArray();
        $this->assertSame('第1.2节', $lessonArray[0]->getTitle());
        $this->assertSame('第1.1节', $lessonArray[1]->getTitle());
    }

    /**
     * 创建并持久化包含章节和课时的课程
     */
    private function createPersistedCourseWithLessons(int $chapterCount, int $lessonsPerChapter): Course
    {
        $course = CourseFactory::createWithLessons($chapterCount, $lessonsPerChapter);
        $this->em->persist($course);
        $this->em->flush();

        return $course;
    }
}
