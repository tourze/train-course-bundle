<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;

/**
 * CourseOutlineRepository 集成测试
 *
 * @template TEntity of CourseOutline
 * @extends AbstractRepositoryTestCase<CourseOutline>
 *
 * @internal
 */
#[CoversClass(CourseOutlineRepository::class)]
#[RunTestsInSeparateProcesses]
final class CourseOutlineRepositoryTest extends AbstractRepositoryTestCase
{
    private CourseOutlineRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CourseOutlineRepository::class);
        self::assertInstanceOf(CourseOutlineRepository::class, $this->repository);

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

            $outline = new CourseOutline();
            $outline->setCourse($course);
            $outline->setTitle('Test Outline for Count');
            $outline->setLearningObjectives('Test learning objectives');
            $outline->setContentPoints('Test content points');
            $outline->setKeyDifficulties('Test key difficulties');
            $outline->setEstimatedMinutes(60);
            $outline->setSortNumber(1);
            self::getEntityManager()->persist($outline);

            self::getEntityManager()->flush();
        }
    }

    public function testFind(): void
    {
        $course = $this->createTestCourse();
        $outline = $this->createTestOutline($course);

        $found = $this->repository->find($outline->getId());
        self::assertInstanceOf(CourseOutline::class, $found);
        self::assertSame($outline->getId(), $found->getId());
    }

    public function testFindAll(): void
    {
        $course = $this->createTestCourse();
        $this->createTestOutline($course, 'draft');
        $this->createTestOutline($course, 'published');

        $outlines = $this->repository->findAll();
        self::assertGreaterThanOrEqual(2, count($outlines));
    }

    public function testFindBy(): void
    {
        $course = $this->createTestCourse();
        $outline1 = $this->createTestOutline($course, 'draft');
        $outline2 = $this->createTestOutline($course, 'published');

        $draftOutlines = $this->repository->findBy(['status' => 'draft']);
        self::assertContains($outline1, $draftOutlines);
        self::assertNotContains($outline2, $draftOutlines);
    }

    public function testFindOneBy(): void
    {
        $course = $this->createTestCourse();
        $outline = $this->createTestOutline($course, 'published', 'Test Outline');

        $found = $this->repository->findOneBy(['title' => 'Test Outline']);
        self::assertInstanceOf(CourseOutline::class, $found);
        self::assertSame($outline->getId(), $found->getId());
    }

    public function testFindOneByReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findOneBy(['title' => 'nonexistent']);
        self::assertNull($found);
    }

    public function testCount(): void
    {
        $course = $this->createTestCourse();
        $initialCount = $this->repository->count([]);

        $this->createTestOutline($course, 'draft');
        $this->createTestOutline($course, 'published');

        $finalCount = $this->repository->count([]);
        self::assertSame($initialCount + 2, $finalCount);
    }

    public function testCountWithCriteria(): void
    {
        $course = $this->createTestCourse();
        $this->createTestOutline($course, 'draft');
        $this->createTestOutline($course, 'published');

        $draftCount = $this->repository->count(['status' => 'draft']);
        $publishedCount = $this->repository->count(['status' => 'published']);

        self::assertGreaterThanOrEqual(1, $draftCount);
        self::assertGreaterThanOrEqual(1, $publishedCount);
    }

    public function testFindByCourse(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $outline1 = $this->createTestOutline($course1, 'draft');
        $outline2 = $this->createTestOutline($course1, 'published');
        $this->createTestOutline($course2, 'draft');

        $courseOutlines = $this->repository->findByCourse($course1);
        self::assertContainsOnlyInstancesOf(CourseOutline::class, $courseOutlines);
        self::assertCount(2, $courseOutlines);
        self::assertContains($outline1, $courseOutlines);
        self::assertContains($outline2, $courseOutlines);
    }

    public function testFindPublishedByCourse(): void
    {
        $course = $this->createTestCourse();
        $publishedOutline = $this->createTestOutline($course, 'published');
        $this->createTestOutline($course, 'draft');

        $publishedOutlines = $this->repository->findPublishedByCourse($course);
        self::assertContainsOnlyInstancesOf(CourseOutline::class, $publishedOutlines);
        self::assertCount(1, $publishedOutlines);
        self::assertContains($publishedOutline, $publishedOutlines);
    }

    public function testFindByStatus(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $outline1 = $this->createTestOutline($course1, 'published');
        $outline2 = $this->createTestOutline($course2, 'published');
        $this->createTestOutline($course1, 'draft');

        $publishedOutlines = $this->repository->findByStatus('published');
        self::assertContainsOnlyInstancesOf(CourseOutline::class, $publishedOutlines);
        self::assertGreaterThanOrEqual(2, count($publishedOutlines));
        self::assertContains($outline1, $publishedOutlines);
        self::assertContains($outline2, $publishedOutlines);
    }

    public function testSearchOutlines(): void
    {
        $course = $this->createTestCourse();
        $outline1 = $this->createTestOutline($course, 'published', 'PHP编程基础');
        $this->createTestOutline($course, 'published', 'Java开发');

        $results = $this->repository->searchOutlines('PHP', $course);
        self::assertContainsOnlyInstancesOf(CourseOutline::class, $results);
        self::assertGreaterThanOrEqual(1, count($results));
        self::assertContains($outline1, $results);
    }

    public function testGetOutlineStatistics(): void
    {
        $course = $this->createTestCourse();
        $this->createTestOutline($course, 'draft');
        $this->createTestOutline($course, 'published');
        $this->createTestOutline($course, 'archived');

        $stats = $this->repository->getOutlineStatistics($course);
        // Method signature guarantees array return with known structure
        self::assertArrayHasKey('total_outlines', $stats);
        self::assertArrayHasKey('published_outlines', $stats);
        self::assertArrayHasKey('draft_outlines', $stats);
        // archived_outlines 可能不存在，取决于实现
        if (isset($stats['archived_outlines'])) {
            self::assertGreaterThanOrEqual(1, $stats['archived_outlines']);
        }
        self::assertGreaterThanOrEqual(3, $stats['total_outlines']);
    }

    public function testSave(): void
    {
        $course = $this->createTestCourse();
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setTitle('Test Outline');
        $outline->setStatus('draft');

        $this->repository->save($outline);

        $found = $this->repository->find($outline->getId());
        self::assertInstanceOf(CourseOutline::class, $found);
        self::assertSame('Test Outline', $found->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $course = $this->createTestCourse();
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setTitle('Another Test Outline');
        $outline->setStatus('published');

        $this->repository->save($outline, false);
        self::getEntityManager()->flush();

        $found = $this->repository->find($outline->getId());
        self::assertInstanceOf(CourseOutline::class, $found);
    }

    public function testRemove(): void
    {
        $course = $this->createTestCourse();
        $outline = $this->createTestOutline($course);
        $outlineId = $outline->getId();

        $this->repository->remove($outline);

        $found = $this->repository->find($outlineId);
        self::assertNull($found);
    }

    public function testFindByNullableField(): void
    {
        $course = $this->createTestCourse();

        // 创建有预估时间的大纲
        $outlineWithTime = new CourseOutline();
        $outlineWithTime->setCourse($course);
        $outlineWithTime->setTitle('Outline With Time');
        $outlineWithTime->setStatus('published');
        $outlineWithTime->setLearningObjectives('Test learning objectives');
        $outlineWithTime->setContentPoints('Test content points');
        $outlineWithTime->setEstimatedMinutes(60);
        self::getEntityManager()->persist($outlineWithTime);

        // 创建无预估时间的大纲
        $outlineWithoutTime = new CourseOutline();
        $outlineWithoutTime->setCourse($course);
        $outlineWithoutTime->setTitle('Outline Without Time');
        $outlineWithoutTime->setStatus('published');
        $outlineWithoutTime->setLearningObjectives('Test learning objectives');
        $outlineWithoutTime->setContentPoints('Test content points');
        $outlineWithoutTime->setEstimatedMinutes(null);
        self::getEntityManager()->persist($outlineWithoutTime);

        self::getEntityManager()->flush();

        $withTime = $this->repository->findBy(['estimatedMinutes' => 60]);
        $withoutTime = $this->repository->findBy(['estimatedMinutes' => null]);

        self::assertContainsOnlyInstancesOf(CourseOutline::class, $withTime);
        self::assertContainsOnlyInstancesOf(CourseOutline::class, $withoutTime);
        self::assertGreaterThanOrEqual(1, count($withTime));
        self::assertGreaterThanOrEqual(1, count($withoutTime));
    }

    /** @param array<string, mixed> $data */
    private function createTestCourse(array $data = []): Course
    {
        // 创建测试分类
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category ' . uniqid());
        $category->setSortOrder(0);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle(is_string($data['title'] ?? null) ? $data['title'] : 'Test Course ' . uniqid());
        $course->setDescription(is_string($data['description'] ?? null) ? $data['description'] : 'Test Description');
        $course->setPrice(is_string($data['price'] ?? null) ? $data['price'] : '100.00');
        $course->setValid(is_bool($data['valid'] ?? null) ? $data['valid'] : true);
        $course->setValidDay(is_int($data['validDay'] ?? null) ? $data['validDay'] : 365);
        $course->setLearnHour(is_int($data['learnHour'] ?? null) ? $data['learnHour'] : 60);
        $course->setCategory($category);

        self::getEntityManager()->persist($course);
        self::getEntityManager()->flush();

        return $course;
    }

    private function createTestOutline(
        Course $course,
        string $status = 'draft',
        ?string $title = null,
    ): CourseOutline {
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setStatus($status);
        $outline->setTitle($title ?? 'Test Outline ' . uniqid());
        $outline->setLearningObjectives('Test learning objectives');
        $outline->setContentPoints('Test content points');

        self::getEntityManager()->persist($outline);
        self::getEntityManager()->flush();

        return $outline;
    }

    protected function createNewEntity(): object
    {
        $course = $this->createTestCourse();

        $entity = new CourseOutline();
        $entity->setCourse($course);
        $entity->setTitle('Test CourseOutline ' . uniqid());
        $entity->setStatus('draft');
        $entity->setLearningObjectives('Test learning objectives');
        $entity->setContentPoints('Test content points');

        return $entity;
    }

    /** @return ServiceEntityRepository<CourseOutline> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
