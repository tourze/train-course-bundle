<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Service\LessonService;

/**
 * @internal
 */
#[CoversClass(LessonService::class)]
#[RunTestsInSeparateProcesses]
final class LessonServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    /**
     * 创建测试用的课程，包含章节和课时
     */
    private function createTestCourse(int $chapterCount = 1, int $lessonsPerChapter = 2): Course
    {
        $em = self::getEntityManager();

        // 创建分类类型
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('测试类型');
        $em->persist($catalogType);

        // 创建分类
        $category = new Catalog();
        $category->setName('测试分类');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        $em->persist($category);

        // 创建课程
        $course = new Course();
        $course->setTitle('测试课程');
        $course->setDescription('测试课程描述');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('99.99');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $em->persist($course);

        // 创建章节和课时
        for ($i = 1; $i <= $chapterCount; ++$i) {
            $chapter = new Chapter();
            $chapter->setCourse($course);
            $chapter->setTitle("第{$i}章 测试章节");
            $chapter->setSortNumber($i);
            $chapter->setCreateTime(new \DateTimeImmutable());
            $chapter->setUpdateTime(new \DateTimeImmutable());
            $em->persist($chapter);

            for ($j = 1; $j <= $lessonsPerChapter; ++$j) {
                $lesson = new Lesson();
                $lesson->setChapter($chapter);
                $lesson->setTitle("第{$i}.{$j}节 测试课时");
                $lesson->setVideoUrl("https://example.com/video/{$i}_{$j}.mp4");
                $lesson->setDurationSecond(1800);
                $lesson->setSortNumber($j);
                $lesson->setCreateTime(new \DateTimeImmutable());
                $lesson->setUpdateTime(new \DateTimeImmutable());
                $em->persist($lesson);
            }
        }

        $em->flush();

        return $course;
    }

    public function testServiceExists(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 验证服务的公共方法数量符合预期
        $reflection = new \ReflectionClass($lessonService);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $publicMethodNames = array_map(fn ($method) => $method->getName(), $publicMethods);

        // 验证核心方法存在
        $this->assertContains('findById', $publicMethodNames);
        $this->assertContains('findOneBy', $publicMethodNames);

        // 验证业务方法存在
        $this->assertContains('belongsToCourse', $publicMethodNames);
        $this->assertContains('findByChapter', $publicMethodNames);
        $this->assertContains('findByCourse', $publicMethodNames);
        $this->assertContains('findLessonsWithVideo', $publicMethodNames);
    }

    public function testServiceCanBeRetrieved(): void
    {
        $lessonService = self::getService(LessonService::class);
        $this->assertInstanceOf(LessonService::class, $lessonService);
    }

    public function testFindById(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 创建测试数据
        $course = $this->createTestCourse(1, 2);
        $em = self::getEntityManager();

        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $this->assertCount(1, $chapters);

        $lessonRepository = self::getService('Tourze\TrainCourseBundle\Repository\LessonRepository');
        $lessons = $lessonRepository->findBy(['chapter' => $chapters[0]]);
        $this->assertCount(2, $lessons);

        $lesson = $lessons[0];
        $lessonId = $lesson->getId();
        $this->assertNotNull($lessonId);

        // 测试findById - 存在的ID
        $result = $lessonService->findById($lessonId);
        $this->assertNotNull($result);
        $this->assertInstanceOf(Lesson::class, $result);
        $this->assertEquals($lessonId, $result->getId());

        // 测试findById - 不存在的ID
        $nonExistentId = 'non_existent_id_' . uniqid();
        $result = $lessonService->findById($nonExistentId);
        $this->assertNull($result);
    }

    public function testFindOneBy(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 创建测试数据
        $course = $this->createTestCourse(1, 3);
        $em = self::getEntityManager();

        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $lessonRepository = self::getService('Tourze\TrainCourseBundle\Repository\LessonRepository');
        $lessons = $lessonRepository->findBy(['chapter' => $chapters[0]]);
        $lesson = $lessons[0];

        // 测试findOneBy - 根据标题查找
        $result = $lessonService->findOneBy(['title' => $lesson->getTitle()]);
        $this->assertNotNull($result);
        $this->assertInstanceOf(Lesson::class, $result);
        $this->assertEquals($lesson->getTitle(), $result->getTitle());

        // 测试findOneBy - 查找不存在的记录
        $result = $lessonService->findOneBy(['title' => 'non_existent_title_' . uniqid()]);
        $this->assertNull($result);

        // 测试findOneBy - 带排序
        $result = $lessonService->findOneBy([], ['sortNumber' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertInstanceOf(Lesson::class, $result);
    }

    public function testFindBy(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 创建测试数据
        $course = $this->createTestCourse(2, 3);
        $em = self::getEntityManager();

        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $chapter1 = $chapters[0];

        // 测试findBy - 根据章节查找
        $result = $lessonService->findBy(['chapter' => $chapter1]);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertEquals($chapter1->getId(), $lesson->getChapter()->getId());
        }

        // 测试findBy - 带排序
        $result = $lessonService->findBy(['chapter' => $chapter1], ['sortNumber' => 'ASC']);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // 验证排序
        for ($i = 0; $i < count($result) - 1; ++$i) {
            $this->assertLessThanOrEqual(
                $result[$i + 1]->getSortNumber(),
                $result[$i]->getSortNumber()
            );
        }

        // 测试findBy - 带限制和偏移
        $result = $lessonService->findBy(['chapter' => $chapter1], ['sortNumber' => 'ASC'], 2, 1);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // 测试findBy - 空结果
        $emptyChapter = new Chapter();
        $emptyChapter->setTitle('Empty Chapter');
        $result = $lessonService->findBy(['chapter' => $emptyChapter]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindAll(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 创建测试数据
        $this->createTestCourse(1, 2);
        $this->createTestCourse(1, 3);

        // 测试findAll
        $result = $lessonService->findAll();
        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(5, count($result)); // 至少5个lesson

        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertNotNull($lesson->getId());
            $this->assertNotEmpty($lesson->getTitle());
        }
    }

    public function testFindByChapter(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 创建测试数据
        $course = $this->createTestCourse(2, 4);
        $em = self::getEntityManager();

        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $chapter1 = $chapters[0];
        $chapter2 = $chapters[1];

        // 测试findByChapter - 第一个章节
        $result = $lessonService->findByChapter($chapter1);
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertEquals($chapter1->getId(), $lesson->getChapter()->getId());
        }

        // 测试findByChapter - 第二个章节
        $result = $lessonService->findByChapter($chapter2);
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertEquals($chapter2->getId(), $lesson->getChapter()->getId());
        }

        // 测试findByChapter - 空章节
        $emptyChapter = new Chapter();
        $emptyChapter->setTitle('Empty Chapter');
        $emptyChapter->setCourse($course);
        $em->persist($emptyChapter);
        $em->flush();

        $result = $lessonService->findByChapter($emptyChapter);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindByCourse(): void
    {
        $lessonService = self::getService(LessonService::class);

        // 创建测试数据
        $course1 = $this->createTestCourse(2, 3); // 2章，每章3课时 = 6课时
        $course2 = $this->createTestCourse(1, 4); // 1章，每章4课时 = 4课时

        // 测试findByCourse - 第一门课程
        $result = $lessonService->findByCourse($course1);
        $this->assertIsArray($result);
        $this->assertCount(6, $result);

        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertEquals($course1->getId(), $lesson->getChapter()->getCourse()->getId());
        }

        // 测试findByCourse - 第二门课程
        $result = $lessonService->findByCourse($course2);
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $this->assertEquals($course2->getId(), $lesson->getChapter()->getCourse()->getId());
        }

        // 测试findByCourse - 没有课时的课程
        $emptyCourse = $this->createTestCourse(0, 0);

        $result = $lessonService->findByCourse($emptyCourse);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindByVideoProtocol(): void
    {
        $lessonService = self::getService(LessonService::class);
        $em = self::getEntityManager();

        // 创建有不同视频协议的测试数据
        $course = $this->createTestCourse(1, 0); // 先创建没有课时的课程
        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $chapter = $chapters[0];

        // 创建不同协议的课时
        $lesson1 = new Lesson();
        $lesson1->setTitle('HTTP视频课时');
        $lesson1->setVideoUrl('https://example.com/video1.mp4');
        $lesson1->setDurationSecond(1800);
        $lesson1->setChapter($chapter);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Polyv视频课时');
        $lesson2->setVideoUrl('polyv://dp-video/12345');
        $lesson2->setDurationSecond(2400);
        $lesson2->setChapter($chapter);
        $lesson2->setSortNumber(2);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('阿里云视频课时');
        $lesson3->setVideoUrl('ali://vod/67890');
        $lesson3->setDurationSecond(3000);
        $lesson3->setChapter($chapter);
        $lesson3->setSortNumber(3);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lesson3);

        $em->flush();

        // 测试findByVideoProtocol - https协议
        $result = $lessonService->findByVideoProtocol('https://');
        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));

        $found = false;
        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            if (str_starts_with($lesson->getVideoUrl() ?? '', 'https://')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Should find at least one lesson with https protocol');

        // 测试findByVideoProtocol - polyv协议
        $result = $lessonService->findByVideoProtocol('polyv://');
        $this->assertIsArray($result);

        if (count($result) > 0) {
            foreach ($result as $lesson) {
                $this->assertInstanceOf(Lesson::class, $lesson);
                $this->assertStringStartsWith('polyv://', $lesson->getVideoUrl() ?? '');
            }
        }

        // 测试findByVideoProtocol - 不存在的协议
        $result = $lessonService->findByVideoProtocol('unknown://');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindLessonsWithVideo(): void
    {
        $lessonService = self::getService(LessonService::class);
        $em = self::getEntityManager();

        // 创建测试数据
        $course = $this->createTestCourse(1, 0); // 先创建没有课时的课程
        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $chapter = $chapters[0];

        // 创建有视频的课时
        $lessonWithVideo1 = new Lesson();
        $lessonWithVideo1->setTitle('有视频的课时1');
        $lessonWithVideo1->setVideoUrl('https://example.com/video1.mp4');
        $lessonWithVideo1->setDurationSecond(1800);
        $lessonWithVideo1->setChapter($chapter);
        $lessonWithVideo1->setSortNumber(1);
        $lessonWithVideo1->setCreateTime(new \DateTimeImmutable());
        $lessonWithVideo1->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lessonWithVideo1);

        $lessonWithVideo2 = new Lesson();
        $lessonWithVideo2->setTitle('有视频的课时2');
        $lessonWithVideo2->setVideoUrl('polyv://dp-video/12345');
        $lessonWithVideo2->setDurationSecond(2400);
        $lessonWithVideo2->setChapter($chapter);
        $lessonWithVideo2->setSortNumber(2);
        $lessonWithVideo2->setCreateTime(new \DateTimeImmutable());
        $lessonWithVideo2->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lessonWithVideo2);

        // 创建没有视频的课时
        $lessonWithoutVideo1 = new Lesson();
        $lessonWithoutVideo1->setTitle('没有视频的课时1');
        $lessonWithoutVideo1->setVideoUrl(null);
        $lessonWithoutVideo1->setDurationSecond(0);
        $lessonWithoutVideo1->setChapter($chapter);
        $lessonWithoutVideo1->setSortNumber(3);
        $lessonWithoutVideo1->setCreateTime(new \DateTimeImmutable());
        $lessonWithoutVideo1->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lessonWithoutVideo1);

        $lessonWithoutVideo2 = new Lesson();
        $lessonWithoutVideo2->setTitle('没有视频的课时2');
        $lessonWithoutVideo2->setVideoUrl('');
        $lessonWithoutVideo2->setDurationSecond(0);
        $lessonWithoutVideo2->setChapter($chapter);
        $lessonWithoutVideo2->setSortNumber(4);
        $lessonWithoutVideo2->setCreateTime(new \DateTimeImmutable());
        $lessonWithoutVideo2->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lessonWithoutVideo2);

        $em->flush();

        // 测试findLessonsWithVideo
        $result = $lessonService->findLessonsWithVideo($chapter);
        $this->assertIsArray($result);

        // 验证返回的课时都有视频URL
        foreach ($result as $lesson) {
            $this->assertInstanceOf(Lesson::class, $lesson);
            $videoUrl = $lesson->getVideoUrl();
            $this->assertNotNull($videoUrl);
            $this->assertNotEmpty($videoUrl);
            $this->assertEquals($chapter->getId(), $lesson->getChapter()->getId());
        }

        // 验证找到的有视频课时数量合理
        $this->assertGreaterThanOrEqual(2, count($result));
    }

    public function testSearchLessons(): void
    {
        $lessonService = self::getService(LessonService::class);
        $em = self::getEntityManager();

        // 创建测试数据
        $course = $this->createTestCourse(1, 0); // 先创建没有课时的课程
        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $chapters = $chapterRepository->findBy(['course' => $course]);
        $chapter = $chapters[0];

        $lesson1 = new Lesson();
        $lesson1->setTitle('安全生产基础知识');
        $lesson1->setVideoUrl('https://example.com/safety1.mp4');
        $lesson1->setDurationSecond(1800);
        $lesson1->setChapter($chapter);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('消防安全知识');
        $lesson2->setVideoUrl('https://example.com/fire.mp4');
        $lesson2->setDurationSecond(2100);
        $lesson2->setChapter($chapter);
        $lesson2->setSortNumber(2);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('职业健康培训');
        $lesson3->setVideoUrl('https://example.com/health.mp4');
        $lesson3->setDurationSecond(2400);
        $lesson3->setChapter($chapter);
        $lesson3->setSortNumber(3);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $em->persist($lesson3);

        $em->flush();

        // 测试searchLessons - 关键词搜索
        $result = $lessonService->searchLessons('安全');
        $this->assertIsArray($result);

        if (count($result) > 0) {
            foreach ($result as $lesson) {
                $this->assertInstanceOf(Lesson::class, $lesson);
                $this->assertStringContainsString('安全', $lesson->getTitle());
            }
        }

        // 测试searchLessons - 指定章节搜索
        $result = $lessonService->searchLessons('培训', $chapter);
        $this->assertIsArray($result);

        if (count($result) > 0) {
            foreach ($result as $lesson) {
                $this->assertInstanceOf(Lesson::class, $lesson);
                $this->assertEquals($chapter->getId(), $lesson->getChapter()->getId());
                $this->assertStringContainsString('培训', $lesson->getTitle());
            }
        }

        // 测试searchLessons - 找不到结果
        $result = $lessonService->searchLessons('不存在的关键词' . uniqid());
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        // 测试searchLessons - 空关键词
        $result = $lessonService->searchLessons('');
        $this->assertIsArray($result);
    }

    public function testBelongsToCourse(): void
    {
        $lessonService = self::getService(LessonService::class);
        $em = self::getEntityManager();

        // 创建测试数据
        $course1 = $this->createTestCourse(1, 2);
        $course2 = $this->createTestCourse(1, 2);

        $chapterRepository = self::getService('Tourze\TrainCourseBundle\Repository\ChapterRepository');
        $lessonRepository = self::getService('Tourze\TrainCourseBundle\Repository\LessonRepository');

        $chapters1 = $chapterRepository->findBy(['course' => $course1]);
        $chapters2 = $chapterRepository->findBy(['course' => $course2]);

        $lessons1 = $lessonRepository->findBy(['chapter' => $chapters1[0]]);
        $lessons2 = $lessonRepository->findBy(['chapter' => $chapters2[0]]);

        $lesson1 = $lessons1[0];
        $lesson2 = $lessons2[0];

        // 测试belongsToCourse - 属于该课程
        $result = $lessonService->belongsToCourse($lesson1, $course1);
        $this->assertTrue($result);

        // 测试belongsToCourse - 不属于该课程
        $result = $lessonService->belongsToCourse($lesson1, $course2);
        $this->assertFalse($result);

        // 测试belongsToCourse - 第二个课程的课时
        $result = $lessonService->belongsToCourse($lesson2, $course2);
        $this->assertTrue($result);

        $result = $lessonService->belongsToCourse($lesson2, $course1);
        $this->assertFalse($result);
    }
}
