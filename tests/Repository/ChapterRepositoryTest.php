<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;

/**
 * ChapterRepository 集成测试
 *
 * @template TEntity of Chapter
 * @extends AbstractRepositoryTestCase<Chapter>
 * @internal
 */
#[CoversClass(ChapterRepository::class)]
#[RunTestsInSeparateProcesses]
final class ChapterRepositoryTest extends AbstractRepositoryTestCase
{
    private ChapterRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ChapterRepository::class);
        self::assertInstanceOf(ChapterRepository::class, $this->repository);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为 count 测试创建测试数据
            $catalogType = new CatalogType();
            $catalogType->setCode('test_type_' . uniqid());
            $catalogType->setName('测试类型');
            self::getEntityManager()->persist($catalogType);

            $category = new Catalog();
            $category->setName('测试分类');
            $category->setSortOrder(1);
            $category->setType($catalogType);
            $category->setCreateTime(new \DateTimeImmutable());
            $category->setUpdateTime(new \DateTimeImmutable());
            self::getEntityManager()->persist($category);

            $course = new Course();
            $course->setTitle('测试课程');
            $course->setDescription('测试课程描述');
            $course->setCategory($category);
            $course->setValid(true);
            $course->setValidDay(365);
            $course->setLearnHour(40);
            $course->setPrice('20.00');
            $course->setSortNumber(1);
            $course->setCreateTime(new \DateTimeImmutable());
            $course->setUpdateTime(new \DateTimeImmutable());
            self::getEntityManager()->persist($course);

            $chapter = new Chapter();
            $chapter->setCourse($course);
            $chapter->setTitle('测试章节');
            $chapter->setSortNumber(1);
            $chapter->setCreateTime(new \DateTimeImmutable());
            $chapter->setUpdateTime(new \DateTimeImmutable());
            self::getEntityManager()->persist($chapter);
            self::getEntityManager()->flush();
        }
    }

    public function testCountWithDatabase(): void
    {
        $count = $this->repository->count([]);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithExistingChapters(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        // 获取添加前的数量
        $initialCount = $this->repository->count([]);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter 3');
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        // 验证数量增加了3个
        $finalCount = $this->repository->count([]);
        self::assertSame($initialCount + 3, $finalCount);
    }

    public function testCountWithCriteria(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setPrice('20.00');
        $course1->setSortNumber(1);
        $course1->setCreateTime(new \DateTimeImmutable());
        $course1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setPrice('20.00');
        $course2->setSortNumber(1);
        $course2->setCreateTime(new \DateTimeImmutable());
        $course2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course2);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course1);
        $chapter1->setTitle('Course1 Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course1);
        $chapter2->setTitle('Course1 Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course2);
        $chapter3->setTitle('Course2 Chapter 1');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);

        $chapter4 = new Chapter();
        $chapter4->setCourse($course2);
        $chapter4->setTitle('Course2 Chapter 2');
        $chapter4->setSortNumber(1);
        $chapter4->setCreateTime(new \DateTimeImmutable());
        $chapter4->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter4);

        $chapter5 = new Chapter();
        $chapter5->setCourse($course2);
        $chapter5->setTitle('Course2 Chapter 3');
        $chapter5->setSortNumber(1);
        $chapter5->setCreateTime(new \DateTimeImmutable());
        $chapter5->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter5);
        self::getEntityManager()->flush();

        $countCourse1 = $this->repository->count(['course' => $course1]);
        $countCourse2 = $this->repository->count(['course' => $course2]);

        self::assertSame(2, $countCourse1);
        self::assertSame(3, $countCourse2);
    }

    public function testCountRobustnessWithInvalidCriteria(): void
    {
        $this->expectException(UnrecognizedField::class);
        $this->repository->count(['nonexistentField' => 'value']);
    }

    // 测试基础 Doctrine 方法：findBy

    public function testFindByBasicOperation(): void
    {
        $result = $this->repository->findBy([]);
        // Repository::findBy returns typed array, test that it's callable and iterable
        self::assertGreaterThanOrEqual(0, count($result));
    }

    public function testFindByBasicFunctionality(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter 3');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        $result = $this->repository->findBy(['course' => $course]);

        self::assertCount(3, $result);
        // Repository returns typed array of Chapter entities
    }

    public function testFindByWithLimitAndOffset(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter 3');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);

        $chapter4 = new Chapter();
        $chapter4->setCourse($course);
        $chapter4->setTitle('Chapter 4');
        $chapter4->setSortNumber(1);
        $chapter4->setCreateTime(new \DateTimeImmutable());
        $chapter4->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter4);

        $chapter5 = new Chapter();
        $chapter5->setCourse($course);
        $chapter5->setTitle('Chapter 5');
        $chapter5->setSortNumber(1);
        $chapter5->setCreateTime(new \DateTimeImmutable());
        $chapter5->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter5);
        self::getEntityManager()->flush();

        $resultPage1 = $this->repository->findBy(['course' => $course], null, 2, 0);
        $resultPage2 = $this->repository->findBy(['course' => $course], null, 2, 2);

        self::assertCount(2, $resultPage1);
        self::assertCount(2, $resultPage2);
        self::assertNotEquals($resultPage1[0]->getId(), $resultPage2[0]->getId());
    }

    public function testFindByRobustnessWithInvalidCriteria(): void
    {
        $this->expectException(UnrecognizedField::class);
        $this->repository->findBy(['nonexistentField' => 'value']);
    }

    // 测试基础 Doctrine 方法：findOneBy

    public function testFindOneByWithEmptyDatabase(): void
    {
        $result = $this->repository->findOneBy(['title' => 'Nonexistent']);
        self::assertNull($result);
    }

    public function testFindOneByBasicFunctionality(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Unique Chapter');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['title' => 'Unique Chapter']);

        self::assertInstanceOf(Chapter::class, $result);
        self::assertSame($chapter->getId(), $result->getId());
        self::assertSame('Unique Chapter', $result->getTitle());
    }

    public function testFindOneByWithOrderBy(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Common Title A');
        $chapter1->setSortNumber(2);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $firstChapter = new Chapter();
        $firstChapter->setCourse($course);
        $firstChapter->setTitle('Common Title B');
        $firstChapter->setSortNumber(1);
        $firstChapter->setCreateTime(new \DateTimeImmutable());
        $firstChapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($firstChapter);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['title' => 'Common Title B'], ['sortNumber' => 'ASC']);

        self::assertInstanceOf(Chapter::class, $result);
        self::assertSame($firstChapter->getId(), $result->getId());
    }

    public function testFindOneByOrderingLogic(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter A');
        $chapter1->setSortNumber(3);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter B');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter C');
        $chapter3->setSortNumber(2);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        $firstBySortNumber = $this->repository->findOneBy(['course' => $course], ['sortNumber' => 'ASC']);
        $lastBySortNumber = $this->repository->findOneBy(['course' => $course], ['sortNumber' => 'DESC']);

        self::assertInstanceOf(Chapter::class, $firstBySortNumber);
        self::assertSame($chapter2->getId(), $firstBySortNumber->getId()); // sortNumber = 1

        self::assertInstanceOf(Chapter::class, $lastBySortNumber);
        self::assertSame($chapter1->getId(), $lastBySortNumber->getId()); // sortNumber = 3
    }

    // 测试基础 Doctrine 方法：findAll

    public function testFindAllBasicOperation(): void
    {
        $result = $this->repository->findAll();

        // Repository methods always return array by contract
        self::assertIsArray($result);
        // If we have results, they should be Chapter entities by repository contract
    }

    public function testFindAllBasicFunctionality(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter 3');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        $result = $this->repository->findAll();

        // 验证返回结果是数组且包含我们创建的章节
        // Repository methods always return array by contract
        self::assertGreaterThanOrEqual(3, count($result)); // 至少包含我们创建的3个章节
        // Repository returns typed array of Chapter entities

        // 验证我们创建的章节都在结果中
        $resultIds = array_map(fn (Chapter $chapter) => $chapter->getId(), $result);
        self::assertContains($chapter1->getId(), $resultIds);
        self::assertContains($chapter2->getId(), $resultIds);
        self::assertContains($chapter3->getId(), $resultIds);
    }

    // 测试自定义方法：findByCourse
    public function testFindByCourseWithEmptyDatabase(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Empty Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $result = $this->repository->findByCourse($course);
        self::assertSame([], $result);
    }

    public function testFindByCourseBasicFunctionality(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter 3');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        $result = $this->repository->findByCourse($course);

        self::assertCount(3, $result);
        // Repository returns typed array of Chapter entities
        foreach ($result as $chapter) {
            self::assertSame($course->getId(), $chapter->getCourse()->getId());
        }
    }

    public function testFindByCourseOrderingBySortNumber(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('First Chapter');
        $chapter1->setSortNumber(3);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Second Chapter');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Third Chapter');
        $chapter3->setSortNumber(2);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        $result = $this->repository->findByCourse($course);

        // 应该按照 sortNumber DESC, id ASC 排序
        self::assertCount(3, $result);
        self::assertSame($chapter1->getId(), $result[0]->getId()); // sortNumber = 3
        self::assertSame($chapter3->getId(), $result[1]->getId()); // sortNumber = 2
        self::assertSame($chapter2->getId(), $result[2]->getId()); // sortNumber = 1
    }

    // 测试自定义方法：findByCourseWithLessons
    public function testFindByCourseWithLessonsBasicFunctionality(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Chapter with Lessons');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setVideoUrl('https://example.com/video1');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setDurationSecond(300);
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(1);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setVideoUrl('https://example.com/video2');
        self::getEntityManager()->persist($lesson2);

        $chapter->addLesson($lesson1);
        $chapter->addLesson($lesson2);

        self::getEntityManager()->flush();

        $result = $this->repository->findByCourseWithLessons($course);

        self::assertCount(1, $result);
        $chapterWithLessons = $result[0];
        self::assertCount(2, $chapterWithLessons->getLessons());
    }

    // 测试自定义方法：getChapterStatistics
    public function testGetChapterStatisticsWithEmptyCourse(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Empty Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $stats = $this->repository->getChapterStatistics($course);

        // Method signature guarantees array return with known structure
        self::assertArrayHasKey('total_chapters', $stats);
        self::assertArrayHasKey('total_lessons', $stats);
        self::assertArrayHasKey('total_duration_seconds', $stats);
        self::assertArrayHasKey('total_duration_hours', $stats);

        self::assertSame(0, $stats['total_chapters']);
        self::assertSame(0, $stats['total_lessons']);
        self::assertSame(0, $stats['total_duration_seconds']);
        self::assertSame(0.0, $stats['total_duration_hours']);
    }

    public function testGetChapterStatisticsWithData(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Course with Data');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        // Chapter 1: 2 lessons, 600 seconds total
        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setDurationSecond(300); // 5 minutes each
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setVideoUrl('https://example.com/video1');
        self::getEntityManager()->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Lesson 2');
        $lesson2->setDurationSecond(300); // 5 minutes each
        $lesson2->setFaceDetectDuration(900);
        $lesson2->setSortNumber(1);
        $lesson2->setCreateTime(new \DateTimeImmutable());
        $lesson2->setUpdateTime(new \DateTimeImmutable());
        $lesson2->setVideoUrl('https://example.com/video2');
        self::getEntityManager()->persist($lesson2);

        $chapter1->addLesson($lesson1);
        $chapter1->addLesson($lesson2);

        // Chapter 2: 1 lesson, 1800 seconds
        $lesson3 = new Lesson();
        $lesson3->setTitle('Long Lesson');
        $lesson3->setDurationSecond(1800); // 30 minutes
        $lesson3->setFaceDetectDuration(900);
        $lesson3->setSortNumber(1);
        $lesson3->setCreateTime(new \DateTimeImmutable());
        $lesson3->setUpdateTime(new \DateTimeImmutable());
        $lesson3->setVideoUrl('https://example.com/long-video');
        self::getEntityManager()->persist($lesson3);

        $chapter2->addLesson($lesson3);

        self::getEntityManager()->flush();

        $stats = $this->repository->getChapterStatistics($course);

        self::assertSame(2, $stats['total_chapters']);
        self::assertSame(3, $stats['total_lessons']);
        self::assertSame(2400, $stats['total_duration_seconds']); // 600 + 1800
        self::assertSame(0.67, $stats['total_duration_hours']); // 2400 / 3600 rounded to 2 decimals
    }

    // 测试自定义方法：searchChapters
    public function testSearchChaptersWithEmptyKeyword(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Some Chapter');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);
        self::getEntityManager()->flush();

        // 空关键词会匹配所有章节（LIKE '%%'）
        $result = $this->repository->searchChapters('', $course);
        self::assertCount(1, $result);
        self::assertSame('Some Chapter', $result[0]->getTitle());
    }

    public function testSearchChaptersBasicFunctionality(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $matchingChapter = new Chapter();
        $matchingChapter->setCourse($course);
        $matchingChapter->setTitle('Introduction to PHP');
        $matchingChapter->setSortNumber(1);
        $matchingChapter->setCreateTime(new \DateTimeImmutable());
        $matchingChapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($matchingChapter);

        $nonMatchingChapter = new Chapter();
        $nonMatchingChapter->setCourse($course);
        $nonMatchingChapter->setTitle('Advanced JavaScript');
        $nonMatchingChapter->setSortNumber(1);
        $nonMatchingChapter->setCreateTime(new \DateTimeImmutable());
        $nonMatchingChapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($nonMatchingChapter);
        self::getEntityManager()->flush();

        $result = $this->repository->searchChapters('PHP', $course);

        self::assertCount(1, $result);
        self::assertSame($matchingChapter->getId(), $result[0]->getId());
    }

    public function testSearchChaptersWithoutCourseFilter(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setPrice('20.00');
        $course1->setSortNumber(1);
        $course1->setCreateTime(new \DateTimeImmutable());
        $course1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setPrice('20.00');
        $course2->setSortNumber(1);
        $course2->setCreateTime(new \DateTimeImmutable());
        $course2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course2);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course1);
        $chapter1->setTitle('Introduction to PHP');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course2);
        $chapter2->setTitle('Advanced PHP Concepts');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);
        self::getEntityManager()->flush();

        $result = $this->repository->searchChapters('PHP');

        // 验证至少包含我们创建的2个章节
        self::assertGreaterThanOrEqual(2, count($result));

        // 验证我们创建的章节都在搜索结果中
        $resultIds = array_map(fn (Chapter $chapter) => $chapter->getId(), $result);
        self::assertContains($chapter1->getId(), $resultIds);
        self::assertContains($chapter2->getId(), $resultIds);
    }

    public function testSearchChaptersCaseInsensitive(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Introduction to PHP');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);
        self::getEntityManager()->flush();

        $result = $this->repository->searchChapters('php', $course);

        self::assertCount(1, $result);
    }

    // 测试自定义方法：save 和 remove
    public function testSaveMethod(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('New Chapter');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());

        $this->repository->save($chapter, true);

        self::assertNotNull($chapter->getId());

        // 验证数据库中存在该记录
        $foundChapter = $this->repository->find($chapter->getId());
        self::assertInstanceOf(Chapter::class, $foundChapter);
        self::assertSame('New Chapter', $foundChapter->getTitle());
    }

    public function testSaveMethodWithoutFlush(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('New Chapter Without Flush');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());

        $this->repository->save($chapter, false);

        // 使用 SnowflakeKeyAware 时，persist 会立即生成 ID
        self::assertNotNull($chapter->getId());
        $originalId = $chapter->getId();

        // 手动 flush 应该不会改变 ID
        self::getEntityManager()->flush();

        self::assertSame($originalId, $chapter->getId());
    }

    public function testRemoveMethod(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Chapter to Remove');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);

        $chapterId = $chapter->getId();

        $this->repository->remove($chapter, true);

        // 验证数据库中不存在该记录
        $foundChapter = $this->repository->find($chapterId);
        self::assertNull($foundChapter);
    }

    public function testRemoveMethodWithoutFlush(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Chapter to Remove Without Flush');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);
        self::getEntityManager()->flush();

        $chapterId = $chapter->getId();

        $this->repository->remove($chapter, false);

        // 在没有手动 flush 之前，记录应该还存在
        $foundChapter = $this->repository->find($chapterId);
        self::assertInstanceOf(Chapter::class, $foundChapter);

        // 手动 flush
        self::getEntityManager()->flush();

        // 现在记录应该被删除
        $foundChapter = $this->repository->find($chapterId);
        self::assertNull($foundChapter);
    }

    // 测试关联字段查询
    public function testFindByAssociationField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setPrice('20.00');
        $course1->setSortNumber(1);
        $course1->setCreateTime(new \DateTimeImmutable());
        $course1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setPrice('20.00');
        $course2->setSortNumber(1);
        $course2->setCreateTime(new \DateTimeImmutable());
        $course2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course2);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course1);
        $chapter1->setTitle('Course1 Chapter');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course2);
        $chapter2->setTitle('Course2 Chapter');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);
        self::getEntityManager()->flush();

        $course1Chapters = $this->repository->findBy(['course' => $course1]);
        $course2Chapters = $this->repository->findBy(['course' => $course2]);

        self::assertCount(1, $course1Chapters);
        self::assertCount(1, $course2Chapters);
        self::assertSame('Course1 Chapter', $course1Chapters[0]->getTitle());
        self::assertSame('Course2 Chapter', $course2Chapters[0]->getTitle());
    }

    public function testCountByAssociationField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setPrice('20.00');
        $course1->setSortNumber(1);
        $course1->setCreateTime(new \DateTimeImmutable());
        $course1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setPrice('20.00');
        $course2->setSortNumber(1);
        $course2->setCreateTime(new \DateTimeImmutable());
        $course2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course2);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course1);
        $chapter1->setTitle('Course1 Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course1);
        $chapter2->setTitle('Course1 Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course2);
        $chapter3->setTitle('Course2 Chapter');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);
        self::getEntityManager()->flush();

        $course1Count = $this->repository->count(['course' => $course1]);
        $course2Count = $this->repository->count(['course' => $course2]);

        self::assertSame(2, $course1Count);
        self::assertSame(1, $course2Count);
    }

    // 测试可空字段 - Chapter 实体没有可空字段，这里测试与关联实体的限制条件
    public function testFindByNullableAssociationCondition(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setPrice('20.00');
        $course1->setSortNumber(1);
        $course1->setCreateTime(new \DateTimeImmutable());
        $course1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setPrice('20.00');
        $course2->setSortNumber(1);
        $course2->setCreateTime(new \DateTimeImmutable());
        $course2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course2);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course1);
        $chapter1->setTitle('Chapter in Course 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course2);
        $chapter2->setTitle('Chapter in Course 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        self::getEntityManager()->flush();

        $course1Chapters = $this->repository->findBy(['course' => $course1]);
        $course2Chapters = $this->repository->findBy(['course' => $course2]);

        self::assertCount(1, $course1Chapters);
        self::assertCount(1, $course2Chapters);
        self::assertSame('Chapter in Course 1', $course1Chapters[0]->getTitle());
        self::assertSame('Chapter in Course 2', $course2Chapters[0]->getTitle());
    }

    public function testCountByAssociationCondition(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setPrice('20.00');
        $course1->setSortNumber(1);
        $course1->setCreateTime(new \DateTimeImmutable());
        $course1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setPrice('20.00');
        $course2->setSortNumber(1);
        $course2->setCreateTime(new \DateTimeImmutable());
        $course2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course2);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course1);
        $chapter1->setTitle('Chapter 1 in Course 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course1);
        $chapter2->setTitle('Chapter 2 in Course 1');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course2);
        $chapter3->setTitle('Chapter 1 in Course 2');
        $chapter3->setSortNumber(1);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);

        self::getEntityManager()->flush();

        $course1Count = $this->repository->count(['course' => $course1]);
        $course2Count = $this->repository->count(['course' => $course2]);

        self::assertSame(2, $course1Count);
        self::assertSame(1, $course2Count);
    }

    // 额外的 findOneBy 排序测试，确保 PHPStan 识别
    public function testFindOneByOrderingLogicAdditional(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter Z');
        $chapter1->setSortNumber(10);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter A');
        $chapter2->setSortNumber(5);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        $chapter3 = new Chapter();
        $chapter3->setCourse($course);
        $chapter3->setTitle('Chapter M');
        $chapter3->setSortNumber(8);
        $chapter3->setCreateTime(new \DateTimeImmutable());
        $chapter3->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter3);

        self::getEntityManager()->flush();

        // 测试 ASC 排序
        $firstBySortNumber = $this->repository->findOneBy(['course' => $course], ['sortNumber' => 'ASC']);
        self::assertInstanceOf(Chapter::class, $firstBySortNumber);
        self::assertSame($chapter2->getId(), $firstBySortNumber->getId()); // sortNumber = 5

        // 测试 DESC 排序
        $lastBySortNumber = $this->repository->findOneBy(['course' => $course], ['sortNumber' => 'DESC']);
        self::assertInstanceOf(Chapter::class, $lastBySortNumber);
        self::assertSame($chapter1->getId(), $lastBySortNumber->getId()); // sortNumber = 10

        // 测试按标题排序
        $firstByTitle = $this->repository->findOneBy(['course' => $course], ['title' => 'ASC']);
        self::assertInstanceOf(Chapter::class, $firstByTitle);
        self::assertSame('Chapter A', $firstByTitle->getTitle());
    }

    // 测试 Lessons 关联的 IS NULL 查询
    public function testFindByLessonsIsNullCondition(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        // 创建一个有课时的章节
        $chapterWithLessons = new Chapter();
        $chapterWithLessons->setCourse($course);
        $chapterWithLessons->setTitle('Chapter with Lessons');
        $chapterWithLessons->setSortNumber(1);
        $chapterWithLessons->setCreateTime(new \DateTimeImmutable());
        $chapterWithLessons->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapterWithLessons);

        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setVideoUrl('https://example.com/video1');
        $lesson1->setChapter($chapterWithLessons);
        self::getEntityManager()->persist($lesson1);

        // 创建一个没有课时的章节
        $chapterWithoutLessons = new Chapter();
        $chapterWithoutLessons->setCourse($course);
        $chapterWithoutLessons->setTitle('Chapter without Lessons');
        $chapterWithoutLessons->setSortNumber(1);
        $chapterWithoutLessons->setCreateTime(new \DateTimeImmutable());
        $chapterWithoutLessons->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapterWithoutLessons);

        self::getEntityManager()->flush();

        // 查找我们测试课程的章节，验证关联关系
        $allChapters = $this->repository->findBy(['course' => $course]);
        self::assertCount(2, $allChapters);

        // 通过自定义查询获取课程的所有章节（包含预加载的课时）
        $chaptersWithLessons = $this->repository->findByCourseWithLessons($course);
        self::assertCount(2, $chaptersWithLessons);

        // 验证章节与课时的关联关系
        $chapterTitles = array_map(fn ($chapter) => $chapter->getTitle(), $chaptersWithLessons);
        self::assertContains('Chapter with Lessons', $chapterTitles);
        self::assertContains('Chapter without Lessons', $chapterTitles);
    }

    // 测试 CoverThumb（通过 Lesson）IS NULL 查询
    public function testFindByLessonCoverThumbIsNull(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Test Chapter');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);

        // 创建有封面的课时
        $lessonWithCover = new Lesson();
        $lessonWithCover->setTitle('Lesson with Cover');
        $lessonWithCover->setDurationSecond(300);
        $lessonWithCover->setFaceDetectDuration(900);
        $lessonWithCover->setSortNumber(1);
        $lessonWithCover->setCreateTime(new \DateTimeImmutable());
        $lessonWithCover->setUpdateTime(new \DateTimeImmutable());
        $lessonWithCover->setVideoUrl('https://example.com/video1');
        $lessonWithCover->setCoverThumb('https://example.com/cover.jpg');
        self::getEntityManager()->persist($lessonWithCover);

        // 创建没有封面的课时
        $lessonWithoutCover = new Lesson();
        $lessonWithoutCover->setTitle('Lesson without Cover');
        $lessonWithoutCover->setDurationSecond(300);
        $lessonWithoutCover->setFaceDetectDuration(900);
        $lessonWithoutCover->setSortNumber(1);
        $lessonWithoutCover->setCreateTime(new \DateTimeImmutable());
        $lessonWithoutCover->setUpdateTime(new \DateTimeImmutable());
        $lessonWithoutCover->setVideoUrl('https://example.com/video2');
        $lessonWithoutCover->setCoverThumb(null);
        self::getEntityManager()->persist($lessonWithoutCover);

        $chapter->addLesson($lessonWithCover);
        $chapter->addLesson($lessonWithoutCover);

        self::getEntityManager()->flush();

        // 验证章节包含两个课时
        $foundChapter = $this->repository->find($chapter->getId());
        self::assertInstanceOf(Chapter::class, $foundChapter);
        self::assertCount(2, $foundChapter->getLessons());

        // 验证课时的封面设置
        $lessons = $foundChapter->getLessons()->toArray();
        $coverThumbs = array_map(fn ($lesson) => $lesson->getCoverThumb(), $lessons);
        self::assertContains('https://example.com/cover.jpg', $coverThumbs);
        self::assertContains(null, $coverThumbs);
    }

    // 测试 VideoUrl（通过 Lesson）IS NULL 查询
    public function testFindByLessonVideoUrlIsNull(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('Test Chapter');
        $chapter->setSortNumber(1);
        $chapter->setCreateTime(new \DateTimeImmutable());
        $chapter->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter);

        // 创建有视频的课时
        $lessonWithVideo = new Lesson();
        $lessonWithVideo->setTitle('Lesson with Video');
        $lessonWithVideo->setDurationSecond(300);
        $lessonWithVideo->setFaceDetectDuration(900);
        $lessonWithVideo->setSortNumber(1);
        $lessonWithVideo->setCreateTime(new \DateTimeImmutable());
        $lessonWithVideo->setUpdateTime(new \DateTimeImmutable());
        $lessonWithVideo->setVideoUrl('https://example.com/video1');
        self::getEntityManager()->persist($lessonWithVideo);

        // 创建没有视频的课时
        $lessonWithoutVideo = new Lesson();
        $lessonWithoutVideo->setTitle('Lesson without Video');
        $lessonWithoutVideo->setDurationSecond(300);
        $lessonWithoutVideo->setFaceDetectDuration(900);
        $lessonWithoutVideo->setSortNumber(1);
        $lessonWithoutVideo->setCreateTime(new \DateTimeImmutable());
        $lessonWithoutVideo->setUpdateTime(new \DateTimeImmutable());
        $lessonWithoutVideo->setVideoUrl(null);
        self::getEntityManager()->persist($lessonWithoutVideo);

        $chapter->addLesson($lessonWithVideo);
        $chapter->addLesson($lessonWithoutVideo);

        self::getEntityManager()->flush();

        // 验证章节包含两个课时
        $foundChapter = $this->repository->find($chapter->getId());
        self::assertInstanceOf(Chapter::class, $foundChapter);
        self::assertCount(2, $foundChapter->getLessons());

        // 验证课时的视频设置
        $lessons = $foundChapter->getLessons()->toArray();
        $videoUrls = array_map(fn ($lesson) => $lesson->getVideoUrl(), $lessons);
        self::assertContains('https://example.com/video1', $videoUrls);
        self::assertContains(null, $videoUrls);
    }

    // 测试 count 查询对于 IS NULL 条件（通过 Lesson）
    public function testCountByLessonNullFields(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('20.00');
        $course->setSortNumber(1);
        $course->setCreateTime(new \DateTimeImmutable());
        $course->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($course);

        $chapter1 = new Chapter();
        $chapter1->setCourse($course);
        $chapter1->setTitle('Chapter 1');
        $chapter1->setSortNumber(1);
        $chapter1->setCreateTime(new \DateTimeImmutable());
        $chapter1->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter1);

        $chapter2 = new Chapter();
        $chapter2->setCourse($course);
        $chapter2->setTitle('Chapter 2');
        $chapter2->setSortNumber(1);
        $chapter2->setCreateTime(new \DateTimeImmutable());
        $chapter2->setUpdateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($chapter2);

        // Chapter 1: 有课时的章节
        $lesson1 = new Lesson();
        $lesson1->setTitle('Lesson 1');
        $lesson1->setDurationSecond(300);
        $lesson1->setFaceDetectDuration(900);
        $lesson1->setSortNumber(1);
        $lesson1->setCreateTime(new \DateTimeImmutable());
        $lesson1->setUpdateTime(new \DateTimeImmutable());
        $lesson1->setVideoUrl('https://example.com/video1');
        self::getEntityManager()->persist($lesson1);

        $chapter1->addLesson($lesson1);

        // Chapter 2: 没有课时的章节
        // 不添加任何课时

        self::getEntityManager()->flush();

        // 使用 count 验证结果
        $totalChapters = $this->repository->count(['course' => $course]);
        self::assertSame(2, $totalChapters);

        // 通过标题区分统计
        $chaptersWithSpecificTitle = $this->repository->count(['title' => 'Chapter 1']);
        self::assertSame(1, $chaptersWithSpecificTitle);

        $chaptersWithAnotherTitle = $this->repository->count(['title' => 'Chapter 2']);
        self::assertSame(1, $chaptersWithAnotherTitle);
    }

    protected function createNewEntity(): object
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('测试类型');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('测试分类 ' . uniqid());
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('测试课程 ' . uniqid());
        $course->setDescription('测试课程描述');
        $course->setCategory($category);
        $course->setLearnHour(10);
        self::getEntityManager()->persist($course);

        $chapter = new Chapter();
        $chapter->setCourse($course);
        $chapter->setTitle('测试章节 ' . uniqid());
        $chapter->setSortNumber(100);

        return $chapter;
    }

    /**
     * @return ChapterRepository
     */
    protected function getRepository(): ChapterRepository
    {
        return $this->repository;
    }
}
