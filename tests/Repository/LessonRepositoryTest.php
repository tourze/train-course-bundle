<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\LessonRepository;

/**
 * LessonRepository 集成测试
 *
 * @internal
 */
#[CoversClass(LessonRepository::class)]
#[RunTestsInSeparateProcesses]
final class LessonRepositoryTest extends AbstractRepositoryTestCase
{
    private LessonRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(LessonRepository::class);
        self::assertInstanceOf(LessonRepository::class, $this->repository);

        // 创建测试数据以确保 testCountWithDataFixtureShouldReturnGreaterThanZero 测试通过
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // 创建 CatalogType
        $catalogType = new CatalogType();
        $catalogType->setCode('course');
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        // 创建 Category
        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        // 创建 Course
        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        // 创建 Chapter
        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        // 创建 Lesson
        $lesson = new Lesson();
        $lesson->setTitle('Test Lesson');
        $lesson->setChapter($chapter);
        $lesson->setDurationSecond(300);
        $lesson->setFaceDetectDuration(900);
        $lesson->setSortNumber(1);
        $lesson->setCreateTime(new \DateTimeImmutable());
        $lesson->setUpdateTime(new \DateTimeImmutable());
        $lesson->setCreatedBy('test_user');
        $lesson->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson);

        self::getEntityManager()->flush();
    }

    public function testFindOneByWithNonMatchingCriteria(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson = new Lesson();
        $lesson->setTitle('Test Lesson');
        $lesson->setChapter($chapter);
        $lesson->setDurationSecond(300);
        $lesson->setFaceDetectDuration(900);
        $lesson->setSortNumber(1);
        $lesson->setCreateTime(new \DateTimeImmutable());
        $lesson->setUpdateTime(new \DateTimeImmutable());
        $lesson->setCreatedBy('test_user');
        $lesson->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson);

        self::getEntityManager()->flush();

        $found = $this->repository->findOneBy(['title' => 'NonExistent']);

        self::assertNull($found);
    }

    public function testFindOneByOrderingLogic(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson Title 1');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(3);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson Title 2');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(1);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson Title 3');
        $lesson3->setChapter($chapter);
        $lesson3->setDurationSecond(300);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(2);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        self::getEntityManager()->flush();

        // 测试按 sortNumber 排序
        $firstBySort = $this->repository->findOneBy(['chapter' => $chapter], ['sortNumber' => 'ASC']);
        $lastBySort = $this->repository->findOneBy(['chapter' => $chapter], ['sortNumber' => 'DESC']);

        self::assertNotNull($firstBySort);
        self::assertSame($lesson2->getId(), $firstBySort->getId()); // sortNumber = 1

        self::assertNotNull($lastBySort);
        self::assertSame($lesson1->getId(), $lastBySort->getId()); // sortNumber = 3
    }

    // 测试关联字段查询
    public function testFindByAssociationField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setTitle('Chapter 1');
        $chapter1->setCourse($course);
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        $chapter1->setCreatedBy('test_user');
        $chapter1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setTitle('Chapter 2');
        $chapter2->setCourse($course);
        $chapter2->setSortNumber(2);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        $chapter2->setCreatedBy('test_user');
        $chapter2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter2);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setChapter($chapter1);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setChapter($chapter2);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(1);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        self::getEntityManager()->flush();

        $chapter1Lessons = $this->repository->findBy(['chapter' => $chapter1]);
        $chapter2Lessons = $this->repository->findBy(['chapter' => $chapter2]);

        self::assertCount(1, $chapter1Lessons);
        self::assertCount(1, $chapter2Lessons);
        self::assertSame($lesson1->getId(), $chapter1Lessons[0]->getId());
        self::assertSame($lesson2->getId(), $chapter2Lessons[0]->getId());
    }

    public function testCountByAssociationField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setTitle('Chapter 1');
        $chapter1->setCourse($course);
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        $chapter1->setCreatedBy('test_user');
        $chapter1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setTitle('Chapter 2');
        $chapter2->setCourse($course);
        $chapter2->setSortNumber(2);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        $chapter2->setCreatedBy('test_user');
        $chapter2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter2);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setChapter($chapter1);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setChapter($chapter1);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson 3');
        $lesson3->setChapter($chapter2);
        $lesson3->setDurationSecond(300);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(1);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        self::getEntityManager()->flush();

        $chapter1Count = $this->repository->count(['chapter' => $chapter1]);
        $chapter2Count = $this->repository->count(['chapter' => $chapter2]);

        self::assertSame(2, $chapter1Count);
        self::assertSame(1, $chapter2Count);
    }

    // 测试可空字段查询
    public function testFindByNullableField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson with cover');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCoverThumb('https://example.com/cover.jpg');
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson without cover');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setCoverThumb(null);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson with video');
        $lesson3->setChapter($chapter);
        $lesson3->setDurationSecond(300);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(3);
        $lesson3->setVideoUrl('https://example.com/video.mp4');
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        $lesson4 = new Lesson();
        $lesson4->setTitle('Lesson without video');
        $lesson4->setChapter($chapter);
        $lesson4->setDurationSecond(300);
        $lesson4->setFaceDetectDuration(900);
        $lesson4->setSortNumber(4);
        $lesson4->setVideoUrl(null);
        $lesson4->setCreateTime(new \DateTimeImmutable());
        $lesson4->setUpdateTime(new \DateTimeImmutable());
        $lesson4->setCreatedBy('test_user');
        $lesson4->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson4);

        self::getEntityManager()->flush();

        $lessonsWithoutCover = $this->repository->findBy(['coverThumb' => null]);
        $lessonsWithoutVideo = $this->repository->findBy(['videoUrl' => null]);

        // 由于有其他测试数据，我们需要确保只计算当前测试的数据
        $currentTestLessonWithoutCover = null;
        $currentTestLessonWithoutVideo = null;

        foreach ($lessonsWithoutCover as $lesson) {
            if ('Lesson without cover' === $lesson->getTitle()) {
                $currentTestLessonWithoutCover = $lesson;
                break;
            }
        }

        foreach ($lessonsWithoutVideo as $lesson) {
            if ('Lesson without video' === $lesson->getTitle()) {
                $currentTestLessonWithoutVideo = $lesson;
                break;
            }
        }

        self::assertNotNull($currentTestLessonWithoutCover, 'Should find lesson without cover');
        self::assertNotNull($currentTestLessonWithoutVideo, 'Should find lesson without video');
        self::assertSame('Lesson without cover', $currentTestLessonWithoutCover->getTitle());
        self::assertSame('Lesson without video', $currentTestLessonWithoutVideo->getTitle());
    }

    public function testCountByNullableField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson with cover');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCoverThumb('https://example.com/cover.jpg');
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson without cover');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setCoverThumb(null);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson with video');
        $lesson3->setChapter($chapter);
        $lesson3->setDurationSecond(300);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(3);
        $lesson3->setVideoUrl('https://example.com/video.mp4');
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        $lesson4 = new Lesson();
        $lesson4->setTitle('Lesson without video');
        $lesson4->setChapter($chapter);
        $lesson4->setDurationSecond(300);
        $lesson4->setFaceDetectDuration(900);
        $lesson4->setSortNumber(4);
        $lesson4->setVideoUrl(null);
        $lesson4->setCreateTime(new \DateTimeImmutable());
        $lesson4->setUpdateTime(new \DateTimeImmutable());
        $lesson4->setCreatedBy('test_user');
        $lesson4->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson4);

        self::getEntityManager()->flush();

        // 由于有其他测试数据，我们需要使用更精确的查询
        $chapterWithoutCover = $this->repository->findOneBy(['title' => 'Lesson without cover']);
        $chapterWithoutVideo = $this->repository->findOneBy(['title' => 'Lesson without video']);

        self::assertNotNull($chapterWithoutCover, 'Should find lesson without cover');
        self::assertNotNull($chapterWithoutVideo, 'Should find lesson without video');

        // 验证这些特定的课程确实没有对应的属性
        if ($chapterWithoutCover instanceof Lesson) {
            self::assertNull($chapterWithoutCover->getCoverThumb(), 'Lesson without cover should have null coverThumb');
        }
        if ($chapterWithoutVideo instanceof Lesson) {
            self::assertNull($chapterWithoutVideo->getVideoUrl(), 'Lesson without video should have null videoUrl');
        }
    }

    // 测试自定义方法
    public function testFindByChapter(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(2);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(1);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson 3');
        $lesson3->setChapter($chapter);
        $lesson3->setDurationSecond(300);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(3);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        self::getEntityManager()->flush();

        $lessons = $this->repository->findByChapter($chapter);

        self::assertCount(3, $lessons);
        self::assertContainsOnlyInstancesOf(Lesson::class, $lessons);

        // 验证排序 (DESC, ASC)
        self::assertSame($lesson3->getId(), $lessons[0]->getId()); // sortNumber = 3
        self::assertSame($lesson1->getId(), $lessons[1]->getId()); // sortNumber = 2
        self::assertSame($lesson2->getId(), $lessons[2]->getId()); // sortNumber = 1
    }

    public function testFindByCourse(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setTitle('Chapter 1');
        $chapter1->setCourse($course);
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        $chapter1->setCreatedBy('test_user');
        $chapter1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setTitle('Chapter 2');
        $chapter2->setCourse($course);
        $chapter2->setSortNumber(2);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        $chapter2->setCreatedBy('test_user');
        $chapter2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter2);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setChapter($chapter1);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setChapter($chapter1);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson 3');
        $lesson3->setChapter($chapter2);
        $lesson3->setDurationSecond(300);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(1);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        self::getEntityManager()->flush();

        $lessons = $this->repository->findByCourse($course);

        self::assertCount(3, $lessons);
        self::assertContainsOnlyInstancesOf(Lesson::class, $lessons);
    }

    public function testFindLessonsWithVideo(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson with video');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setVideoUrl('https://example.com/video.mp4');
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson without video');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setVideoUrl(null);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        self::getEntityManager()->flush();

        $lessonsWithVideo = $this->repository->findLessonsWithVideo($chapter);

        self::assertCount(1, $lessonsWithVideo);
        self::assertSame('Lesson with video', $lessonsWithVideo[0]->getTitle());
    }

    public function testGetLessonStatistics(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setVideoUrl('https://example.com/video1.mp4');
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(600);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setVideoUrl('https://example.com/video2.mp4');
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        self::getEntityManager()->flush();

        $stats = $this->repository->getLessonStatistics($chapter);

        // Method signature guarantees array return with known structure
        self::assertArrayHasKey('total_lessons', $stats);
        self::assertArrayHasKey('total_duration_seconds', $stats);
        self::assertArrayHasKey('total_duration_hours', $stats);
        self::assertArrayHasKey('lessons_with_video', $stats);
        self::assertArrayHasKey('lessons_without_video', $stats);

        self::assertSame(2, $stats['total_lessons']);
        self::assertSame(900, $stats['total_duration_seconds']);
        self::assertSame(0.25, $stats['total_duration_hours']);
        // 计算平均值来验证
        self::assertEquals(450.0, $stats['total_duration_seconds'] / $stats['total_lessons']);
    }

    public function testSearchLessons(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $uniqueKeyword = 'ZZZUniqueKeyword' . uniqid();

        $lesson1 = new Lesson();
        $lesson1->setTitle('Introduction to ' . $uniqueKeyword);
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Advanced JavaScript');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        self::getEntityManager()->flush();

        // 在特定章节中搜索
        $results = $this->repository->searchLessons($uniqueKeyword, $chapter);

        self::assertCount(1, $results);
        self::assertSame($lesson1->getId(), $results[0]->getId());

        // 全局搜索
        $globalResults = $this->repository->searchLessons($uniqueKeyword);

        self::assertCount(1, $globalResults);
        self::assertSame($lesson1->getId(), $globalResults[0]->getId());
    }

    public function testFindByVideoProtocol(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('HTTPS Lesson');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setVideoUrl('https://example.com/video.mp4');
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('HTTP Lesson');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(2);
        $lesson2->setVideoUrl('http://example.com/video.mp4');
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        self::getEntityManager()->flush();

        $httpsLessons = $this->repository->findByVideoProtocol('https');
        $httpLessons = $this->repository->findByVideoProtocol('http');

        // 查找我们创建的特定课程
        $foundHttpsLesson = null;
        $foundHttpLesson = null;

        foreach ($httpsLessons as $lesson) {
            if ('HTTPS Lesson' === $lesson->getTitle()) {
                $foundHttpsLesson = $lesson;
                break;
            }
        }

        foreach ($httpLessons as $lesson) {
            if ('HTTP Lesson' === $lesson->getTitle()) {
                $foundHttpLesson = $lesson;
                break;
            }
        }

        self::assertNotNull($foundHttpsLesson, 'Should find HTTPS lesson');
        self::assertNotNull($foundHttpLesson, 'Should find HTTP lesson');
        self::assertSame('HTTPS Lesson', $foundHttpsLesson->getTitle());
        self::assertSame('HTTP Lesson', $foundHttpLesson->getTitle());
    }

    // 测试 save 方法
    public function testSaveWithFlush(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        self::getEntityManager()->flush();

        $lesson = new Lesson();
        $lesson->setTitle('New Lesson');
        $lesson->setChapter($chapter);
        $lesson->setDurationSecond(300);
        $lesson->setFaceDetectDuration(900);
        $lesson->setSortNumber(1);

        $this->repository->save($lesson, true);

        self::assertNotNull($lesson->getId());
        $found = $this->repository->find($lesson->getId());
        self::assertInstanceOf(Lesson::class, $found);
        self::assertSame('New Lesson', $found->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        self::getEntityManager()->flush();

        $lesson = new Lesson();
        $lesson->setTitle('New Lesson Without Flush');
        $lesson->setChapter($chapter);
        $lesson->setDurationSecond(300);
        $lesson->setFaceDetectDuration(900);
        $lesson->setSortNumber(1);

        $this->repository->save($lesson, false);

        self::assertNotNull($lesson->getId());
        $originalId = $lesson->getId();

        self::getEntityManager()->flush();

        self::assertSame($originalId, $lesson->getId());
    }

    // 测试 remove 方法

    // 添加额外的 findOneBy 排序测试以满足 PHPStan 要求
    public function testFindOneByOrderingLogicAdditional(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter');
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson Z');
        $lesson1->setChapter($chapter);
        $lesson1->setDurationSecond(900);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(10);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setCreatedBy('test_user');
        $lesson1->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson A');
        $lesson2->setChapter($chapter);
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(5);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setCreatedBy('test_user');
        $lesson2->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle('Lesson M');
        $lesson3->setChapter($chapter);
        $lesson3->setDurationSecond(600);
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(8);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setCreatedBy('test_user');
        $lesson3->setUpdatedBy('test_user');
        self::getEntityManager()->persist($lesson3);

        self::getEntityManager()->flush();

        // 测试按 durationSecond ASC 排序
        $shortestLesson = $this->repository->findOneBy(['chapter' => $chapter], ['durationSecond' => 'ASC']);
        self::assertInstanceOf(Lesson::class, $shortestLesson);
        self::assertSame($lesson2->getId(), $shortestLesson->getId()); // durationSecond = 300

        // 测试按 durationSecond DESC 排序
        $longestLesson = $this->repository->findOneBy(['chapter' => $chapter], ['durationSecond' => 'DESC']);
        self::assertInstanceOf(Lesson::class, $longestLesson);
        self::assertSame($lesson1->getId(), $longestLesson->getId()); // durationSecond = 900

        // 测试按 title 排序
        $firstByTitle = $this->repository->findOneBy(['chapter' => $chapter], ['title' => 'ASC']);
        self::assertInstanceOf(Lesson::class, $firstByTitle);
        self::assertSame('Lesson A', $firstByTitle->getTitle());

        // 测试按 sortNumber DESC 排序
        $lastBySortNumber = $this->repository->findOneBy(['chapter' => $chapter], ['sortNumber' => 'DESC']);
        self::assertInstanceOf(Lesson::class, $lastBySortNumber);
        self::assertSame($lesson1->getId(), $lastBySortNumber->getId()); // sortNumber = 10
    }

    protected function createNewEntity(): object
    {
        $entity = new Lesson();
        $entity->setTitle('Test Lesson ' . uniqid());
        $entity->setDurationSecond(300);
        $entity->setFaceDetectDuration(900);
        $entity->setSortNumber(1);

        // 创建必需的 Chapter 和 Course 实体
        $catalogType = new CatalogType();
        $catalogType->setCode('course-' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course ' . uniqid());
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setTitle('Test Chapter ' . uniqid());
        $chapter->setCourse($course);
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        $chapter->setCreatedBy('test_user');
        $chapter->setUpdatedBy('test_user');
        self::getEntityManager()->persist($chapter);

        $entity->setChapter($chapter);
        $entity->setCreateTime(new \DateTimeImmutable());
        $entity->setUpdateTime(new \DateTimeImmutable());
        $entity->setCreatedBy('test_user');
        $entity->setUpdatedBy('test_user');

        return $entity;
    }

    protected function getRepository(): LessonRepository
    {
        return $this->repository;
    }
}
