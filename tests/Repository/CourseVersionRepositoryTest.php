<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseVersion;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;

/**
 * CourseVersionRepository 集成测试
 *
 * @template TEntity of CourseVersion
 * @extends AbstractRepositoryTestCase<CourseVersion>
 *
 * @internal
 */
#[CoversClass(CourseVersionRepository::class)]
#[RunTestsInSeparateProcesses]
final class CourseVersionRepositoryTest extends AbstractRepositoryTestCase
{
    private CourseVersionRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CourseVersionRepository::class);
        self::assertInstanceOf(CourseVersionRepository::class, $this->repository);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为 count 测试创建测试数据
            $catalogType = new CatalogType();
            $catalogType->setCode('test_course_category_' . uniqid());
            $catalogType->setName('课程分类');
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

            $version = new CourseVersion();
            $version->setCourse($course);
            $version->setVersion('1.0.0');
            $version->setTitle('Test Version for Count');
            $version->setDescription('Test version description');
            self::getEntityManager()->persist($version);

            self::getEntityManager()->flush();
        }
    }

    public function testFindByStatusReturnsArray(): void
    {
        $versions = $this->repository->findByStatus('published');
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);
        self::assertIsArray($versions);
        // All returned versions should have the specified status
        foreach ($versions as $version) {
            self::assertSame('published', $version->getStatus());
        }
    }

    public function testSearchVersionsReturnsArray(): void
    {
        $versions = $this->repository->searchVersions('test', null);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);
        self::assertIsArray($versions);
        // All returned versions should contain the search keyword in version, title, or description
        foreach ($versions as $version) {
            $foundInVersion = stripos($version->getVersion(), 'test') !== false;
            $foundInTitle = $version->getTitle() && stripos($version->getTitle(), 'test') !== false;
            $foundInDescription = $version->getDescription() && stripos($version->getDescription(), 'test') !== false;
            self::assertTrue($foundInVersion || $foundInTitle || $foundInDescription,
                'Search keyword "test" should be found in version, title, or description');
        }
    }

    public function testFindOldVersionsReturnsArray(): void
    {
        $versions = $this->repository->findOldVersions(30);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);
        self::assertIsArray($versions);
        // All returned versions should be older than the specified days
        $cutoffDate = new \DateTime();
        $cutoffDate->modify('-30 days');
        foreach ($versions as $version) {
            $createTime = $version->getCreateTime();
            self::assertInstanceOf(\DateTimeInterface::class, $createTime);
            self::assertLessThanOrEqual($cutoffDate, $createTime);
        }
    }

    public function testFindByCourse(): void
    {
        $entity = $this->createNewEntity();
        $course = $entity->getCourse();
        self::assertInstanceOf(Course::class, $course);

        $this->repository->save($entity);

        $versions = $this->repository->findByCourse($course);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);

        if ([] !== $versions) {
            self::assertSame($course, $versions[0]->getCourse());
        }
    }

    public function testFindByVersion(): void
    {
        $entity = $this->createNewEntity();
        $course = $entity->getCourse();
        $version = $entity->getVersion();
        self::assertInstanceOf(Course::class, $course);
        self::assertIsString($version);

        $this->repository->save($entity);

        $result = $this->repository->findByVersion($course, $version);
        self::assertInstanceOf(CourseVersion::class, $result);
        self::assertSame($course, $result->getCourse());
        self::assertSame($version, $result->getVersion());

        // Test with non-existent version
        $nonExistentResult = $this->repository->findByVersion($course, 'non-existent');
        self::assertNull($nonExistentResult);
    }

    public function testFindCurrentByCourse(): void
    {
        $entity = $this->createNewEntity();
        $course = $entity->getCourse();
        self::assertInstanceOf(Course::class, $course);

        // Set as current version
        $entity->setIsCurrent(true);
        $this->repository->save($entity);

        $result = $this->repository->findCurrentByCourse($course);
        self::assertInstanceOf(CourseVersion::class, $result);
        self::assertSame($course, $result->getCourse());
        self::assertTrue($result->isIsCurrent());

        // Test with course that has no current version
        $anotherEntity = $this->createNewEntity();
        $anotherCourse = $anotherEntity->getCourse();
        self::assertInstanceOf(Course::class, $anotherCourse);
        $anotherEntity->setIsCurrent(false);
        $this->repository->save($anotherEntity);

        $nonCurrentResult = $this->repository->findCurrentByCourse($anotherCourse);
        self::assertNull($nonCurrentResult);
    }

    public function testFindLatestByCourse(): void
    {
        $entity = $this->createNewEntity();
        $course = $entity->getCourse();
        self::assertInstanceOf(Course::class, $course);

        $this->repository->save($entity);

        $result = $this->repository->findLatestByCourse($course);
        self::assertInstanceOf(CourseVersion::class, $result);
        self::assertSame($course, $result->getCourse());

        // Create another version for the same course
        $anotherEntity = new CourseVersion();
        $anotherEntity->setVersion('2.0.0');
        $anotherEntity->setTitle('Another Test Version ' . uniqid());
        $anotherEntity->setDescription('Another test version description');
        $anotherEntity->setCourse($course);

        // Set a later create time to ensure it's the latest
        $laterTime = new \DateTimeImmutable('+1 second');
        $anotherEntity->setCreateTime($laterTime);

        $this->repository->save($anotherEntity);

        $latestResult = $this->repository->findLatestByCourse($course);
        self::assertInstanceOf(CourseVersion::class, $latestResult);
        self::assertSame('2.0.0', $latestResult->getVersion());
    }

    public function testFindPublishedByCourse(): void
    {
        $entity = $this->createNewEntity();
        $course = $entity->getCourse();
        self::assertInstanceOf(Course::class, $course);

        // Set as published
        $entity->setStatus('published');
        $entity->setPublishedAt(new \DateTimeImmutable());
        $this->repository->save($entity);

        $versions = $this->repository->findPublishedByCourse($course);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versions);

        if ([] !== $versions) {
            self::assertSame($course, $versions[0]->getCourse());
            self::assertSame('published', $versions[0]->getStatus());
        }

        // Create a draft version that should not be included
        $draftEntity = new CourseVersion();
        $draftEntity->setVersion('2.0.0');
        $draftEntity->setTitle('Draft Version ' . uniqid());
        $draftEntity->setDescription('Draft version description');
        $draftEntity->setCourse($course);
        $draftEntity->setStatus('draft');
        $this->repository->save($draftEntity);

        $publishedVersions = $this->repository->findPublishedByCourse($course);
        $publishedCount = count($publishedVersions);

        // Should still only return published versions
        foreach ($publishedVersions as $version) {
            self::assertSame('published', $version->getStatus());
        }
    }

    public function testFindVersionsToArchive(): void
    {
        $entity = $this->createNewEntity();
        $course = $entity->getCourse();
        self::assertInstanceOf(Course::class, $course);

        // Create multiple versions to test archiving
        for ($i = 1; $i <= 12; ++$i) {
            $version = new CourseVersion();
            $version->setVersion("1.0.{$i}");
            $version->setTitle("Test Version {$i}");
            $version->setDescription("Test version {$i} description");
            $version->setCourse($course);
            $version->setStatus('published');
            $version->setIsCurrent(12 === $i); // Only the last one is current

            usleep(10000); // Small delay to ensure different create times
            $this->repository->save($version, false);
        }
        self::getEntityManager()->flush();

        // Test with keep count of 5 - should return older versions beyond the 5 newest
        $versionsToArchive = $this->repository->findVersionsToArchive($course, 5);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versionsToArchive);
        self::assertContainsOnlyInstancesOf(CourseVersion::class, $versionsToArchive);

        // Should return 6 versions (12 total - 1 current - 5 to keep = 6)
        $expectedCount = 6;
        self::assertCount($expectedCount, $versionsToArchive);

        // All returned versions should not be current
        foreach ($versionsToArchive as $version) {
            self::assertFalse($version->isIsCurrent());
            self::assertNotSame('archived', $version->getStatus());
        }

        // Test with higher keep count - should return fewer or no versions
        $versionsToArchiveHighKeep = $this->repository->findVersionsToArchive($course, 15);
        self::assertEmpty($versionsToArchiveHighKeep);
    }

    protected function createNewEntity(): CourseVersion
    {
        $entity = new CourseVersion();
        $entity->setVersion('1.0.0');
        $entity->setTitle('Test Version ' . uniqid());
        $entity->setDescription('Test version description');

        // 创建必需的 Category 实体
        $catalogType = new CatalogType();
        $catalogType->setCode('test_course_category_' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        // 创建必需的 Course 实体
        $course = new Course();
        $course->setTitle('Test Course ' . uniqid());
        $course->setDescription('Test Description');
        $course->setPrice('100.0');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(40);
        $course->setSortNumber(1);
        self::getEntityManager()->persist($course);

        $entity->setCourse($course);

        return $entity;
    }

    /** @return ServiceEntityRepository<CourseVersion> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
