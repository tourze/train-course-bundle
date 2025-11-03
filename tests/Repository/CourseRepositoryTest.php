<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\CourseRepository;

/**
 * CourseRepository 集成测试
 *
 * @template TEntity of Course
 * @extends AbstractRepositoryTestCase<Course>
 *
 * @internal
 */
#[CoversClass(CourseRepository::class)]
#[RunTestsInSeparateProcesses]
final class CourseRepositoryTest extends AbstractRepositoryTestCase
{
    private CourseRepository $courseRepository;

    protected function onSetUp(): void
    {
        $this->courseRepository = self::getService(CourseRepository::class);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为 count 测试创建测试数据
            $catalogType = new CatalogType();
            $catalogType->setCode('test_type_' . uniqid());
            $catalogType->setName('Test Type');
            self::getEntityManager()->persist($catalogType);

            $category = new Catalog();
            $category->setName('Test Category for Count');
            $category->setSortOrder(1);
            $category->setType($catalogType);
            self::getEntityManager()->persist($category);

            $course = new Course();
            $course->setTitle('Test Course for Count');
            $course->setDescription('Test Description');
            $course->setPrice('100.0');
            $course->setCategory($category);
            $course->setValid(true);
            $course->setValidDay(365);
            $course->setLearnHour(40);
            $course->setSortNumber(1);
            self::getEntityManager()->persist($course);

            self::getEntityManager()->flush();
        }
    }

    public function testFindValidCoursesReturnsArray(): void
    {
        $courses = $this->courseRepository->findValidCourses();
        self::assertIsArray($courses);
    }

    public function testSearchCoursesReturnsArray(): void
    {
        $courses = $this->courseRepository->searchCourses('test', null);
        self::assertIsArray($courses);
    }

    public function testSearchCoursesWithEmptyKeyword(): void
    {
        $courses = $this->courseRepository->searchCourses('', null);
        self::assertIsArray($courses);
    }

    public function testGetStatisticsReturnsArray(): void
    {
        $stats = $this->courseRepository->getStatistics();

        // 验证返回数组结构
        self::assertArrayHasKey('total_courses', $stats);
        self::assertArrayHasKey('valid_courses', $stats);
        self::assertArrayHasKey('invalid_courses', $stats);

        // 验证数值逻辑关系
        self::assertGreaterThanOrEqual(0, $stats['total_courses']);
        self::assertGreaterThanOrEqual(0, $stats['valid_courses']);
        self::assertGreaterThanOrEqual(0, $stats['invalid_courses']);
        self::assertSame($stats['total_courses'], $stats['valid_courses'] + $stats['invalid_courses']);
    }

    public function testFindByPriceRangeReturnsArray(): void
    {
        $courses = $this->courseRepository->findByPriceRange(0, 100);
        self::assertIsArray($courses);
    }

    public function testFindByPriceRangeWithZeroRange(): void
    {
        $courses = $this->courseRepository->findByPriceRange(0, 0);
        self::assertIsArray($courses);
    }

    public function testCreateBaseQueryBuilderReturnsQueryBuilder(): void
    {
        $qb = $this->courseRepository->createBaseQueryBuilder();
        self::assertNotEmpty($qb);
        self::assertSame('SELECT c, cat, ch, l FROM Tourze\TrainCourseBundle\Entity\Course c LEFT JOIN c.category cat LEFT JOIN c.chapters ch LEFT JOIN ch.lessons l', $qb->getDQL());
    }

    public function testFindUpdatedSinceWithValidDate(): void
    {
        $date = new \DateTime('-7 days');
        $courses = $this->courseRepository->findUpdatedSince($date);
        self::assertIsArray($courses);
    }

    public function testFindExpiredCoursesReturnsArray(): void
    {
        $courses = $this->courseRepository->findExpiredCourses();
        self::assertIsArray($courses);
    }

    // 测试关联字段查询
    public function testFindByAssociationField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category1 = new Catalog();
        $category1->setName('Category 1');
        $category1->setSortOrder(1);
        $category1->setType($catalogType);
        self::getEntityManager()->persist($category1);

        $category2 = new Catalog();
        $category2->setName('Category 2');
        $category2->setSortOrder(2);
        $category2->setType($catalogType);
        self::getEntityManager()->persist($category2);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category1);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category2);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        self::getEntityManager()->persist($course2);

        self::getEntityManager()->flush();

        $category1Courses = $this->courseRepository->findBy(['category' => $category1]);
        $category2Courses = $this->courseRepository->findBy(['category' => $category2]);

        self::assertCount(1, $category1Courses);
        self::assertCount(1, $category2Courses);
        self::assertSame($course1->getId(), $category1Courses[0]->getId());
        self::assertSame($course2->getId(), $category2Courses[0]->getId());
    }

    public function testCountByAssociationField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category1 = new Catalog();
        $category1->setName('Category 1');
        $category1->setSortOrder(1);
        $category1->setType($catalogType);
        self::getEntityManager()->persist($category1);

        $category2 = new Catalog();
        $category2->setName('Category 2');
        $category2->setSortOrder(2);
        $category2->setType($catalogType);
        self::getEntityManager()->persist($category2);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category1);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category1);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course 3');
        $course3->setCategory($category2);
        $course3->setDescription('Test Description');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        self::getEntityManager()->flush();

        $category1Count = $this->courseRepository->count(['category' => $category1]);
        $category2Count = $this->courseRepository->count(['category' => $category2]);

        self::assertSame(2, $category1Count);
        self::assertSame(1, $category2Count);
    }

    // 测试可空字段查询
    public function testFindByNullableField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course with teacher');
        $course1->setCategory($category);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        $course1->setTeacherName('John Doe');
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course without teacher');
        $course2->setCategory($category);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        $course2->setTeacherName(null);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course with desc');
        $course3->setCategory($category);
        $course3->setDescription('Some description');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        $course4 = new Course();
        $course4->setTitle('Course without desc');
        $course4->setCategory($category);
        $course4->setDescription(null);
        $course4->setPrice('100.0');
        $course4->setValid(true);
        $course4->setValidDay(365);
        $course4->setLearnHour(40);
        $course4->setSortNumber(4);
        self::getEntityManager()->persist($course4);

        self::getEntityManager()->flush();

        $coursesWithoutTeacher = $this->courseRepository->findBy(['teacherName' => null]);
        $coursesWithoutDesc = $this->courseRepository->findBy(['description' => null]);

        self::assertGreaterThanOrEqual(1, $coursesWithoutTeacher);
        self::assertGreaterThanOrEqual(1, $coursesWithoutDesc);

        // 验证我们创建的记录在结果中
        $foundTeacher = false;
        foreach ($coursesWithoutTeacher as $course) {
            if ('Course without teacher' === $course->getTitle()) {
                $foundTeacher = true;
                break;
            }
        }
        self::assertTrue($foundTeacher, '创建的无老师记录应该在结果中');

        $foundDesc = false;
        foreach ($coursesWithoutDesc as $course) {
            if ('Course without desc' === $course->getTitle()) {
                $foundDesc = true;
                break;
            }
        }
        self::assertTrue($foundDesc, '创建的无描述记录应该在结果中');
    }

    public function testCountByNullableField(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course with teacher');
        $course1->setCategory($category);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        $course1->setTeacherName('John Doe');
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course without teacher');
        $course2->setCategory($category);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        $course2->setTeacherName(null);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course with desc');
        $course3->setCategory($category);
        $course3->setDescription('Some description');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        $course4 = new Course();
        $course4->setTitle('Course without desc');
        $course4->setCategory($category);
        $course4->setDescription(null);
        $course4->setPrice('100.0');
        $course4->setValid(true);
        $course4->setValidDay(365);
        $course4->setLearnHour(40);
        $course4->setSortNumber(4);
        self::getEntityManager()->persist($course4);

        self::getEntityManager()->flush();

        $countWithoutTeacher = $this->courseRepository->count(['teacherName' => null]);
        $countWithoutDesc = $this->courseRepository->count(['description' => null]);

        self::assertGreaterThanOrEqual(1, $countWithoutTeacher);
        self::assertGreaterThanOrEqual(1, $countWithoutDesc);
    }

    // 添加缺失的测试用例以满足 PHPStan 要求

    // 额外的 findOneBy 排序测试
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
        self::getEntityManager()->persist($category);

        $course1 = new Course();
        $course1->setTitle('Course Z');
        $course1->setCategory($category);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.00');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(10);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course A');
        $course2->setCategory($category);
        $course2->setDescription('Test Description');
        $course2->setPrice('50.00');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(5);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course M');
        $course3->setCategory($category);
        $course3->setDescription('Test Description');
        $course3->setPrice('75.00');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(8);
        self::getEntityManager()->persist($course3);

        self::getEntityManager()->flush();

        // 测试按 sortNumber ASC 排序
        $firstBySortNumber = $this->courseRepository->findOneBy(['category' => $category], ['sortNumber' => 'ASC']);
        self::assertInstanceOf(Course::class, $firstBySortNumber);
        self::assertSame($course2->getId(), $firstBySortNumber->getId()); // sortNumber = 5

        // 测试按 sortNumber DESC 排序
        $lastBySortNumber = $this->courseRepository->findOneBy(['category' => $category], ['sortNumber' => 'DESC']);
        self::assertInstanceOf(Course::class, $lastBySortNumber);
        self::assertSame($course1->getId(), $lastBySortNumber->getId()); // sortNumber = 10

        // 测试按 title 排序
        $firstByTitle = $this->courseRepository->findOneBy(['category' => $category], ['title' => 'ASC']);
        self::assertInstanceOf(Course::class, $firstByTitle);
        self::assertSame('Course A', $firstByTitle->getTitle());

        // 测试按 price 排序
        $cheapest = $this->courseRepository->findOneBy(['category' => $category], ['price' => 'ASC']);
        self::assertInstanceOf(Course::class, $cheapest);
        self::assertSame('50.00', $cheapest->getPrice());
    }

    // 测试 findByCategories 方法
    public function testFindByCategories(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category1 = new Catalog();
        $category1->setName('Category 1');
        $category1->setSortOrder(1);
        $category1->setType($catalogType);
        self::getEntityManager()->persist($category1);

        $category2 = new Catalog();
        $category2->setName('Category 2');
        $category2->setSortOrder(2);
        $category2->setType($catalogType);
        self::getEntityManager()->persist($category2);

        $category3 = new Catalog();
        $category3->setName('Category 3');
        $category3->setSortOrder(3);
        $category3->setType($catalogType);
        self::getEntityManager()->persist($category3);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category1);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category2);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course 3');
        $course3->setCategory($category1);
        $course3->setDescription('Test Description');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        $course4 = new Course();
        $course4->setTitle('Course 4');
        $course4->setCategory($category3);
        $course4->setDescription('Test Description');
        $course4->setPrice('100.0');
        $course4->setValid(true);
        $course4->setValidDay(365);
        $course4->setLearnHour(40);
        $course4->setSortNumber(4);
        self::getEntityManager()->persist($course4);

        self::getEntityManager()->flush();

        $courses = $this->courseRepository->findByCategories([$category1, $category2]);

        self::assertCount(3, $courses);

        $foundCourseIds = array_map(fn ($course) => $course->getId(), $courses);
        self::assertContains($course1->getId(), $foundCourseIds);
        self::assertContains($course2->getId(), $foundCourseIds);
        self::assertContains($course3->getId(), $foundCourseIds);
        self::assertNotContains($course4->getId(), $foundCourseIds);
    }

    // 测试 findByCategory 方法
    public function testFindByCategory(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category1 = new Catalog();
        $category1->setName('Category 1');
        $category1->setSortOrder(1);
        $category1->setType($catalogType);
        self::getEntityManager()->persist($category1);

        $category2 = new Catalog();
        $category2->setName('Category 2');
        $category2->setSortOrder(2);
        $category2->setType($catalogType);
        self::getEntityManager()->persist($category2);

        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category1);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category1);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course 3');
        $course3->setCategory($category2);
        $course3->setDescription('Test Description');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        self::getEntityManager()->flush();

        $category1Courses = $this->courseRepository->findByCategory($category1);

        self::assertCount(2, $category1Courses);

        $foundCourseIds = array_map(fn ($course) => $course->getId(), $category1Courses);
        self::assertContains($course1->getId(), $foundCourseIds);
        self::assertContains($course2->getId(), $foundCourseIds);
        self::assertNotContains($course3->getId(), $foundCourseIds);
    }

    // 测试 save 方法
    public function testSave(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('New Course');
        $course->setCategory($category);
        $course->setDescription('Test Description');
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);

        $this->courseRepository->save($course, true);

        self::assertNotNull($course->getId());

        $foundCourse = $this->courseRepository->find($course->getId());
        self::assertInstanceOf(Course::class, $foundCourse);
        self::assertSame('New Course', $foundCourse->getTitle());
    }

    // 测试 save 方法不带 flush
    public function testSaveWithoutFlush(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('New Course Without Flush');
        $course->setCategory($category);
        $course->setDescription('Test Description');
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setPrice('100.00');
        $course->setSortNumber(1);

        $this->courseRepository->save($course, false);

        // 使用 SnowflakeKeyAware 时，persist 会立即生成 ID
        self::assertNotNull($course->getId());
        $originalId = $course->getId();

        // 手动 flush
        self::getEntityManager()->flush();

        self::assertSame($originalId, $course->getId());
    }

    // 测试 remove 方法
    public function testRemove(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Course to Remove');
        $course->setCategory($category);
        $course->setDescription('Test Description');
        $course->setPrice('100.0');
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setSortNumber(1);
        self::getEntityManager()->persist($course);
        self::getEntityManager()->flush();

        $courseId = $course->getId();

        $this->courseRepository->remove($course, true);

        $foundCourse = $this->courseRepository->find($courseId);
        self::assertNull($foundCourse);
    }

    // 测试 remove 方法不带 flush

    // 测试额外的关联查询
    public function testFindByAssociationFieldAdditional(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category1 = new Catalog();
        $category1->setName('Programming');
        $category1->setSortOrder(1);
        $category1->setType($catalogType);
        self::getEntityManager()->persist($category1);

        $category2 = new Catalog();
        $category2->setName('Design');
        $category2->setSortOrder(2);
        $category2->setType($catalogType);
        self::getEntityManager()->persist($category2);

        $course1 = new Course();
        $course1->setTitle('PHP Course');
        $course1->setCategory($category1);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Python Course');
        $course2->setCategory($category1);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('UI Design');
        $course3->setCategory($category2);
        $course3->setDescription('Test Description');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        self::getEntityManager()->flush();

        // 通过分类查找课程
        $programmingCourses = $this->courseRepository->findBy(['category' => $category1]);
        $designCourses = $this->courseRepository->findBy(['category' => $category2]);

        self::assertCount(2, $programmingCourses);
        self::assertCount(1, $designCourses);

        // 验证课程分类正确
        foreach ($programmingCourses as $course) {
            self::assertSame($category1->getId(), $course->getCategory()->getId());
        }

        foreach ($designCourses as $course) {
            self::assertSame($category2->getId(), $course->getCategory()->getId());
        }
    }

    // 测试额外的关联 count 查询
    public function testCountByAssociationFieldAdditional(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category1 = new Catalog();
        $category1->setName('Category 1');
        $category1->setSortOrder(1);
        $category1->setType($catalogType);
        self::getEntityManager()->persist($category1);

        $category2 = new Catalog();
        $category2->setName('Category 2');
        $category2->setSortOrder(2);
        $category2->setType($catalogType);
        self::getEntityManager()->persist($category2);

        // 为 Category 1 创建多个课程
        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category1);
        $course1->setDescription('Test Description');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category1);
        $course2->setDescription('Test Description');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        self::getEntityManager()->persist($course2);

        $course3 = new Course();
        $course3->setTitle('Course 3');
        $course3->setCategory($category1);
        $course3->setDescription('Test Description');
        $course3->setPrice('100.0');
        $course3->setValid(false);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        self::getEntityManager()->persist($course3);

        // 为 Category 2 创建课程
        $course4 = new Course();
        $course4->setTitle('Course 4');
        $course4->setCategory($category2);
        $course4->setDescription('Test Description');
        $course4->setPrice('100.0');
        $course4->setValid(true);
        $course4->setValidDay(365);
        $course4->setLearnHour(40);
        $course4->setSortNumber(4);
        self::getEntityManager()->persist($course4);

        self::getEntityManager()->flush();

        $category1TotalCount = $this->courseRepository->count(['category' => $category1]);
        $category1ValidCount = $this->courseRepository->count(['category' => $category1, 'valid' => true]);
        $category2Count = $this->courseRepository->count(['category' => $category2]);

        self::assertSame(3, $category1TotalCount);
        self::assertSame(2, $category1ValidCount);
        self::assertSame(1, $category2Count);
    }

    // 测试额外的 IS NULL 查询
    public function testFindByNullableFieldsAdditional(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        // 创建有老师名称的课程
        $course1 = new Course();
        $course1->setTitle('Course with Teacher');
        $course1->setCategory($category);
        $course1->setDescription('Great course');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        $course1->setTeacherName('John Doe');
        self::getEntityManager()->persist($course1);

        // 创建无老师名称但有描述的课程
        $course2 = new Course();
        $course2->setTitle('Course without Teacher');
        $course2->setCategory($category);
        $course2->setDescription('Good course');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        $course2->setTeacherName(null);
        self::getEntityManager()->persist($course2);

        // 创建既无老师名称也无描述的课程
        $course3 = new Course();
        $course3->setTitle('Course without Teacher and Description');
        $course3->setCategory($category);
        $course3->setDescription(null);
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        $course3->setTeacherName(null);
        self::getEntityManager()->persist($course3);

        // 创建有老师但无描述的课程
        $course4 = new Course();
        $course4->setTitle('Course with Teacher but no Description');
        $course4->setCategory($category);
        $course4->setDescription(null);
        $course4->setPrice('100.0');
        $course4->setValid(true);
        $course4->setValidDay(365);
        $course4->setLearnHour(40);
        $course4->setSortNumber(4);
        $course4->setTeacherName('Jane Smith');
        self::getEntityManager()->persist($course4);

        self::getEntityManager()->flush();

        // 测试查找无老师名称的课程
        $coursesWithoutTeacher = $this->courseRepository->findBy(['teacherName' => null]);
        self::assertCount(2, $coursesWithoutTeacher);

        // 测试查找无描述的课程
        $coursesWithoutDescription = $this->courseRepository->findBy(['description' => null]);
        self::assertCount(2, $coursesWithoutDescription);

        // 测试查找既无老师也无描述的课程
        $coursesWithoutTeacherAndDescription = $this->courseRepository->findBy(['teacherName' => null, 'description' => null]);
        self::assertCount(1, $coursesWithoutTeacherAndDescription);
        self::assertSame('Course without Teacher and Description', $coursesWithoutTeacherAndDescription[0]->getTitle());
    }

    // 测试额外的 count IS NULL 查询
    public function testCountByNullableFieldsAdditional(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        // 创建有老师名称的课程
        $course1 = new Course();
        $course1->setTitle('Course 1');
        $course1->setCategory($category);
        $course1->setDescription('Description 1');
        $course1->setPrice('100.0');
        $course1->setValid(true);
        $course1->setValidDay(365);
        $course1->setLearnHour(40);
        $course1->setSortNumber(1);
        $course1->setTeacherName('Teacher 1');
        self::getEntityManager()->persist($course1);

        $course2 = new Course();
        $course2->setTitle('Course 2');
        $course2->setCategory($category);
        $course2->setDescription('Description 2');
        $course2->setPrice('100.0');
        $course2->setValid(true);
        $course2->setValidDay(365);
        $course2->setLearnHour(40);
        $course2->setSortNumber(2);
        $course2->setTeacherName('Teacher 2');
        self::getEntityManager()->persist($course2);

        // 创建无老师名称的课程
        $course3 = new Course();
        $course3->setTitle('Course 3');
        $course3->setCategory($category);
        $course3->setDescription('Description 3');
        $course3->setPrice('100.0');
        $course3->setValid(true);
        $course3->setValidDay(365);
        $course3->setLearnHour(40);
        $course3->setSortNumber(3);
        $course3->setTeacherName(null);
        self::getEntityManager()->persist($course3);

        $course4 = new Course();
        $course4->setTitle('Course 4');
        $course4->setCategory($category);
        $course4->setDescription(null);
        $course4->setPrice('100.0');
        $course4->setValid(true);
        $course4->setValidDay(365);
        $course4->setLearnHour(40);
        $course4->setSortNumber(4);
        $course4->setTeacherName(null);
        self::getEntityManager()->persist($course4);

        // 创建无描述的课程
        $course5 = new Course();
        $course5->setTitle('Course 5');
        $course5->setCategory($category);
        $course5->setDescription(null);
        $course5->setPrice('100.0');
        $course5->setValid(true);
        $course5->setValidDay(365);
        $course5->setLearnHour(40);
        $course5->setSortNumber(5);
        $course5->setTeacherName('Teacher 5');
        self::getEntityManager()->persist($course5);

        self::getEntityManager()->flush();

        $countWithoutTeacher = $this->courseRepository->count(['teacherName' => null]);
        $countWithoutDescription = $this->courseRepository->count(['description' => null]);
        $countWithTeacher = $this->courseRepository->count(['teacherName' => 'Teacher 1']);

        self::assertSame(2, $countWithoutTeacher);
        self::assertSame(2, $countWithoutDescription);
        self::assertSame(1, $countWithTeacher);
    }

    protected function createNewEntity(): object
    {
        $entity = new Course();
        $entity->setTitle('Test Course ' . uniqid());
        $entity->setDescription('Test course description');
        $entity->setPrice('100.00');
        $entity->setValid(true);
        $entity->setValidDay(365);
        $entity->setLearnHour(40);
        $entity->setSortNumber(1);

        // 创建必需的 Category 实体
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $entity->setCategory($category);

        return $entity;
    }

    /** @return ServiceEntityRepository<Course> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->courseRepository;
    }
}
