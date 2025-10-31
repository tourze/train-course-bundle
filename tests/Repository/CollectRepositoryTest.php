<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\CollectRepository;

/**
 * CollectRepository 集成测试
 *
 * @internal
 */
#[CoversClass(CollectRepository::class)]
#[RunTestsInSeparateProcesses]
final class CollectRepositoryTest extends AbstractRepositoryTestCase
{
    private CollectRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CollectRepository::class);
        self::assertInstanceOf(CollectRepository::class, $this->repository);

        // 为 DataFixture 测试准备数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            $this->createTestDataForCountTest();
        }
    }

    private function createTestDataForCountTest(): void
    {
        $now = new \DateTimeImmutable();
        $catalogType = new CatalogType();
        $catalogType->setCode('test_type_' . uniqid());
        $catalogType->setName('Test Type');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        $category->setCreateTime($now);
        $category->setUpdateTime($now);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setCategory($category);
        $course->setCreateTime($now);
        $course->setUpdateTime($now);
        $course->setCreatedBy('test_user');
        $course->setUpdatedBy('test_user');
        $course->setLearnHour(40);
        self::getEntityManager()->persist($course);

        $collect = new Collect();
        $collect->setUserId('test_user');
        $collect->setCourse($course);
        $collect->setStatus('active');
        $collect->setCreateTime($now);
        $collect->setUpdateTime($now);
        $collect->setCreatedBy('test_user');
        $collect->setUpdatedBy('test_user');
        self::getEntityManager()->persist($collect);
        self::getEntityManager()->flush();
    }

    public function testFind(): void
    {
        $course = $this->createTestCourse();
        $collect = $this->createTestCollect('user123', $course);

        $found = $this->repository->find($collect->getId());
        self::assertInstanceOf(Collect::class, $found);
        self::assertSame($collect->getId(), $found->getId());
    }

    public function testFindAll(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user1', $course);
        $this->createTestCollect('user2', $course);

        $collects = $this->repository->findAll();
        self::assertIsArray($collects);
        self::assertGreaterThanOrEqual(2, count($collects));
        self::assertContainsOnlyInstancesOf(Collect::class, $collects);
    }

    public function testFindBy(): void
    {
        $course = $this->createTestCourse();
        $collect1 = $this->createTestCollect('user123', $course, 'active');
        $collect2 = $this->createTestCollect('user456', $course, 'cancelled');

        $activeCollects = $this->repository->findBy(['status' => 'active']);
        self::assertIsArray($activeCollects);
        self::assertContains($collect1, $activeCollects);
        self::assertNotContains($collect2, $activeCollects);
    }

    public function testFindOneBy(): void
    {
        $course = $this->createTestCourse();
        $collect = $this->createTestCollect('user123', $course);

        $found = $this->repository->findOneBy(['userId' => 'user123']);
        self::assertInstanceOf(Collect::class, $found);
        self::assertSame($collect->getId(), $found->getId());
    }

    public function testFindOneByReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findOneBy(['userId' => 'nonexistent']);
        self::assertNull($found);
    }

    public function testFindOneByOrderingLogic(): void
    {
        $course = $this->createTestCourse(['title' => 'Test Course']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course);
        $collect1->setSortNumber(3);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course);
        $collect2->setSortNumber(1);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course);
        $collect3->setSortNumber(2);

        self::getEntityManager()->persist($collect1);
        self::getEntityManager()->persist($collect2);
        self::getEntityManager()->persist($collect3);
        self::getEntityManager()->flush();

        $firstBySortNumber = $this->repository->findOneBy(['course' => $course], ['sortNumber' => 'ASC']);
        $lastBySortNumber = $this->repository->findOneBy(['course' => $course], ['sortNumber' => 'DESC']);

        self::assertInstanceOf(Collect::class, $firstBySortNumber);
        self::assertSame($collect2->getId(), $firstBySortNumber->getId()); // sortNumber = 1

        self::assertInstanceOf(Collect::class, $lastBySortNumber);
        self::assertSame($collect1->getId(), $lastBySortNumber->getId()); // sortNumber = 3
    }

    public function testCount(): void
    {
        $course = $this->createTestCourse();
        $initialCount = $this->repository->count([]);

        $this->createTestCollect('user1', $course);
        $this->createTestCollect('user2', $course);

        $finalCount = $this->repository->count([]);
        self::assertSame($initialCount + 2, $finalCount);
    }

    public function testCountWithCriteria(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user1', $course, 'active');
        $this->createTestCollect('user2', $course, 'cancelled');

        $activeCount = $this->repository->count(['status' => 'active']);
        $cancelledCount = $this->repository->count(['status' => 'cancelled']);

        self::assertGreaterThanOrEqual(1, $activeCount);
        self::assertGreaterThanOrEqual(1, $cancelledCount);
    }

    public function testFindByUser(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $collect1 = $this->createTestCollect('user123', $course1, 'active');
        $collect2 = $this->createTestCollect('user123', $course2, 'active');
        $this->createTestCollect('user456', $course1, 'active');

        $userCollects = $this->repository->findByUser('user123');
        self::assertIsArray($userCollects);
        self::assertCount(2, $userCollects);
        self::assertContains($collect1, $userCollects);
        self::assertContains($collect2, $userCollects);
    }

    public function testFindByUserExcludesInactiveCollects(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $activeCollect = $this->createTestCollect('test_user_excludes_inactive', $course1, 'active');
        $this->createTestCollect('test_user_excludes_inactive', $course2, 'cancelled');

        $userCollects = $this->repository->findByUser('test_user_excludes_inactive');
        self::assertCount(1, $userCollects);
        self::assertContains($activeCollect, $userCollects);
    }

    public function testFindByCourse(): void
    {
        $course = $this->createTestCourse();
        $collect1 = $this->createTestCollect('user1', $course, 'active');
        $collect2 = $this->createTestCollect('user2', $course, 'active');

        $courseCollects = $this->repository->findByCourse($course);
        self::assertIsArray($courseCollects);
        self::assertCount(2, $courseCollects);
        self::assertContains($collect1, $courseCollects);
        self::assertContains($collect2, $courseCollects);
    }

    public function testFindByUserAndCourse(): void
    {
        $course = $this->createTestCourse();
        $collect = $this->createTestCollect('user123', $course);

        $found = $this->repository->findByUserAndCourse('user123', $course);
        self::assertInstanceOf(Collect::class, $found);
        self::assertSame($collect->getId(), $found->getId());
    }

    public function testFindByUserAndCourseReturnsNullWhenNotFound(): void
    {
        $course = $this->createTestCourse();

        $found = $this->repository->findByUserAndCourse('nonexistent', $course);
        self::assertNull($found);
    }

    public function testIsCollectedByUser(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user123', $course, 'active');

        $isCollected = $this->repository->isCollectedByUser('user123', $course);
        self::assertTrue($isCollected);
    }

    public function testIsCollectedByUserReturnsFalseForInactiveCollect(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user123', $course, 'cancelled');

        $isCollected = $this->repository->isCollectedByUser('user123', $course);
        self::assertFalse($isCollected);
    }

    public function testIsCollectedByUserReturnsFalseWhenNotFound(): void
    {
        $course = $this->createTestCourse();

        $isCollected = $this->repository->isCollectedByUser('nonexistent', $course);
        self::assertFalse($isCollected);
    }

    public function testFindByGroup(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $course3 = $this->createTestCourse();
        $collect1 = $this->createTestCollect('test_find_by_group_user', $course1, 'active', 'favorites');
        $collect2 = $this->createTestCollect('test_find_by_group_user', $course2, 'active', 'favorites');
        $this->createTestCollect('test_find_by_group_user', $course3, 'active', 'learning');

        $groupCollects = $this->repository->findByGroup('test_find_by_group_user', 'favorites');
        self::assertIsArray($groupCollects);
        self::assertCount(2, $groupCollects);
        self::assertContains($collect1, $groupCollects);
        self::assertContains($collect2, $groupCollects);
    }

    public function testGetUserCollectGroups(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $course3 = $this->createTestCourse();
        $this->createTestCollect('test_get_user_groups_user', $course1, 'active', 'favorites');
        $this->createTestCollect('test_get_user_groups_user', $course2, 'active', 'favorites');
        $this->createTestCollect('test_get_user_groups_user', $course3, 'active', 'learning');

        $groups = $this->repository->getUserCollectGroups('test_get_user_groups_user');
        self::assertIsArray($groups);
        self::assertGreaterThanOrEqual(2, count($groups));

        $groupNames = array_column($groups, 'group');
        self::assertContains('favorites', $groupNames);
        self::assertContains('learning', $groupNames);
    }

    public function testGetCollectStatistics(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user1', $course, 'active', null, false);
        $this->createTestCollect('user2', $course, 'active', null, true);

        $stats = $this->repository->getCollectStatistics();
        // Method signature guarantees array return with known structure
        self::assertArrayHasKey('total_collects', $stats);
        self::assertArrayHasKey('top_collects', $stats);
        self::assertArrayHasKey('normal_collects', $stats);
        self::assertGreaterThanOrEqual(2, $stats['total_collects']);
        self::assertGreaterThanOrEqual(1, $stats['top_collects']);
    }

    public function testGetCollectStatisticsWithFilters(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user123', $course, 'active');

        $userStats = $this->repository->getCollectStatistics('user123');
        self::assertIsArray($userStats);
        self::assertGreaterThanOrEqual(1, $userStats['total_collects']);

        $courseStats = $this->repository->getCollectStatistics(null, $course);
        self::assertIsArray($courseStats);
        self::assertGreaterThanOrEqual(1, $courseStats['total_collects']);
    }

    public function testSearchCollects(): void
    {
        $course = $this->createTestCourse(['title' => 'PHP编程基础']);
        $collect1 = $this->createTestCollect('user123', $course, 'active');
        $collect1->setNote('学习PHP编程');
        $this->repository->save($collect1);

        $otherCourse = $this->createTestCourse(['title' => 'Java开发']);
        $this->createTestCollect('user123', $otherCourse, 'active');

        $results = $this->repository->searchCollects('user123', 'PHP');
        self::assertIsArray($results);
        self::assertGreaterThanOrEqual(1, count($results));
        self::assertContains($collect1, $results);
    }

    public function testSave(): void
    {
        $course = $this->createTestCourse();
        $collect = new Collect();
        $collect->setUserId('user123');
        $collect->setCourse($course);
        $collect->setStatus('active');

        $this->repository->save($collect);

        $found = $this->repository->find($collect->getId());
        self::assertInstanceOf(Collect::class, $found);
        self::assertSame('user123', $found->getUserId());
    }

    public function testSaveWithoutFlush(): void
    {
        $course = $this->createTestCourse();
        $collect = new Collect();
        $collect->setUserId('user456');
        $collect->setCourse($course);
        $collect->setStatus('active');

        $this->repository->save($collect, false);
        self::getEntityManager()->flush();

        $found = $this->repository->find($collect->getId());
        self::assertInstanceOf(Collect::class, $found);
    }

    public function testRemove(): void
    {
        $course = $this->createTestCourse();
        $collect = $this->createTestCollect('user123', $course);
        $collectId = $collect->getId();

        $this->repository->remove($collect);

        $found = $this->repository->find($collectId);
        self::assertNull($found);
    }

    public function testFindByNullableField(): void
    {
        $course = $this->createTestCourse();
        $this->createTestCollect('user1', $course, 'active', 'group1');
        $this->createTestCollect('user2', $course, 'active');

        $withoutGroup = $this->repository->findBy(['collectGroup' => null]);
        $withGroup = $this->repository->findBy(['collectGroup' => 'group1']);

        self::assertIsArray($withoutGroup);
        self::assertIsArray($withGroup);
        self::assertGreaterThanOrEqual(1, count($withoutGroup));
        self::assertGreaterThanOrEqual(1, count($withGroup));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createTestCourse(array $data = []): Course
    {
        $category = $this->createTestCategory();
        $course = $this->configureCourseData(new Course(), $data);
        $course->setCategory($category);

        self::getEntityManager()->persist($course);
        self::getEntityManager()->flush();

        return $course;
    }

    private function createTestCategory(): Catalog
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

        return $category;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function configureCourseData(Course $course, array $data): Course
    {
        $defaults = [
            'title' => 'Test Course ' . uniqid(),
            'description' => 'Test Description',
            'price' => '100.0',
            'valid' => true,
            'validDay' => 365,
            'learnHour' => 40,
            'sortNumber' => 1,
        ];

        $course->setTitle(is_string($data['title'] ?? null) ? $data['title'] : $defaults['title']);
        $course->setDescription(is_string($data['description'] ?? null) ? $data['description'] : $defaults['description']);
        $course->setPrice(is_string($data['price'] ?? null) ? $data['price'] : $defaults['price']);
        $course->setValid(is_bool($data['valid'] ?? null) ? $data['valid'] : $defaults['valid']);
        $course->setValidDay(is_int($data['validDay'] ?? null) ? $data['validDay'] : $defaults['validDay']);
        $course->setLearnHour(is_int($data['learnHour'] ?? null) ? $data['learnHour'] : $defaults['learnHour']);
        $course->setSortNumber(is_int($data['sortNumber'] ?? null) ? $data['sortNumber'] : $defaults['sortNumber']);

        return $course;
    }

    private function createTestCollect(
        string $userId,
        Course $course,
        string $status = 'active',
        ?string $group = null,
        bool $isTop = false,
    ): Collect {
        $collect = new Collect();
        $collect->setUserId($userId);
        $collect->setCourse($course);
        $collect->setStatus($status);
        $collect->setCollectGroup($group);
        $collect->setIsTop($isTop);

        self::getEntityManager()->persist($collect);
        self::getEntityManager()->flush();

        return $collect;
    }

    // 测试关联字段查询
    public function testFindByAssociationField(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course1);
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course2);
        self::getEntityManager()->persist($collect2);

        self::getEntityManager()->flush();

        $course1Collects = $this->repository->findBy(['course' => $course1]);
        $course2Collects = $this->repository->findBy(['course' => $course2]);

        self::assertCount(1, $course1Collects);
        self::assertCount(1, $course2Collects);
        self::assertSame($collect1->getId(), $course1Collects[0]->getId());
        self::assertSame($collect2->getId(), $course2Collects[0]->getId());
    }

    public function testCountByAssociationField(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course1);
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course1);
        self::getEntityManager()->persist($collect2);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course2);
        self::getEntityManager()->persist($collect3);

        self::getEntityManager()->flush();

        $course1Count = $this->repository->count(['course' => $course1]);
        $course2Count = $this->repository->count(['course' => $course2]);

        self::assertSame(2, $course1Count);
        self::assertSame(1, $course2Count);
    }

    // 测试可空字段查询
    public function testFindByNullableFieldIsNull(): void
    {
        $course = $this->createTestCourse(['title' => 'Test Course']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course);
        $collect1->setCollectGroup('group1');
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course);
        $collect2->setCollectGroup(null);
        self::getEntityManager()->persist($collect2);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course);
        $collect3->setNote('some note');
        self::getEntityManager()->persist($collect3);

        $collect4 = new Collect();
        $collect4->setUserId('user4');
        $collect4->setCourse($course);
        $collect4->setNote(null);
        self::getEntityManager()->persist($collect4);

        self::getEntityManager()->flush();

        $collectsWithoutGroup = $this->repository->findBy(['collectGroup' => null]);
        $collectsWithoutNote = $this->repository->findBy(['note' => null]);

        self::assertCount(3, $collectsWithoutGroup);
        self::assertCount(3, $collectsWithoutNote);
    }

    public function testCountByNullableFieldIsNull(): void
    {
        $course = $this->createTestCourse(['title' => 'Test Course']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course);
        $collect1->setCollectGroup('group1');
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course);
        $collect2->setCollectGroup(null);
        self::getEntityManager()->persist($collect2);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course);
        $collect3->setNote('some note');
        self::getEntityManager()->persist($collect3);

        $collect4 = new Collect();
        $collect4->setUserId('user4');
        $collect4->setCourse($course);
        $collect4->setNote(null);
        self::getEntityManager()->persist($collect4);

        self::getEntityManager()->flush();

        $countWithoutGroup = $this->repository->count(['collectGroup' => null]);
        $countWithoutNote = $this->repository->count(['note' => null]);

        self::assertSame(3, $countWithoutGroup);
        self::assertSame(3, $countWithoutNote);
    }

    // 额外的 findOneBy 排序测试，确保 PHPStan 识别
    public function testFindOneByWithOrderByAdditional(): void
    {
        $course = $this->createTestCourse(['title' => 'Test Course']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course);
        $collect1->setSortNumber(10);
        $collect1->setStatus('active');
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course);
        $collect2->setSortNumber(5);
        $collect2->setStatus('active');
        self::getEntityManager()->persist($collect2);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course);
        $collect3->setSortNumber(8);
        $collect3->setStatus('active');
        self::getEntityManager()->persist($collect3);

        self::getEntityManager()->flush();

        // 测试按 sortNumber ASC 排序
        $firstBySortNumber = $this->repository->findOneBy(['status' => 'active', 'course' => $course], ['sortNumber' => 'ASC']);
        self::assertInstanceOf(Collect::class, $firstBySortNumber);
        self::assertSame($collect2->getId(), $firstBySortNumber->getId()); // sortNumber = 5

        // 测试按 sortNumber DESC 排序
        $lastBySortNumber = $this->repository->findOneBy(['status' => 'active', 'course' => $course], ['sortNumber' => 'DESC']);
        self::assertInstanceOf(Collect::class, $lastBySortNumber);
        self::assertSame($collect1->getId(), $lastBySortNumber->getId()); // sortNumber = 10

        // 测试按 userId 排序
        $firstByUserId = $this->repository->findOneBy(['status' => 'active', 'course' => $course], ['userId' => 'ASC']);
        self::assertInstanceOf(Collect::class, $firstByUserId);
        self::assertSame('user1', $firstByUserId->getUserId());
    }

    // 额外的关联查询测试
    public function testFindByAssociationFieldAdditional(): void
    {
        $course1 = $this->createTestCourse(['title' => 'PHP Course']);
        $course2 = $this->createTestCourse(['title' => 'Java Course']);

        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course1);
        $collect1->setStatus('active');
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course1);
        $collect2->setStatus('active');
        self::getEntityManager()->persist($collect2);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course2);
        $collect3->setStatus('active');
        self::getEntityManager()->persist($collect3);

        self::getEntityManager()->flush();

        // 通过课程查找收藏
        $phpCourseCollects = $this->repository->findBy(['course' => $course1]);
        $javaCourseCollects = $this->repository->findBy(['course' => $course2]);

        self::assertCount(2, $phpCourseCollects);
        self::assertCount(1, $javaCourseCollects);

        // 验证收藏归属正确
        foreach ($phpCourseCollects as $collect) {
            $course = $collect->getCourse();
            self::assertNotNull($course);
            self::assertSame($course1->getId(), $course->getId());
        }

        foreach ($javaCourseCollects as $collect) {
            $course = $collect->getCourse();
            self::assertNotNull($course);
            self::assertSame($course2->getId(), $course->getId());
        }
    }

    // 额外的关联 count 查询测试
    public function testCountByAssociationFieldAdditional(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        // 为 Course 1 创建多个收藏
        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course1);
        $collect1->setStatus('active');
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course1);
        $collect2->setStatus('active');
        self::getEntityManager()->persist($collect2);

        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course1);
        $collect3->setStatus('cancelled');
        self::getEntityManager()->persist($collect3);

        // 为 Course 2 创建收藏
        $collect4 = new Collect();
        $collect4->setUserId('user4');
        $collect4->setCourse($course2);
        $collect4->setStatus('active');
        self::getEntityManager()->persist($collect4);

        self::getEntityManager()->flush();

        $course1TotalCount = $this->repository->count(['course' => $course1]);
        $course1ActiveCount = $this->repository->count(['course' => $course1, 'status' => 'active']);
        $course2Count = $this->repository->count(['course' => $course2]);

        self::assertSame(3, $course1TotalCount);
        self::assertSame(2, $course1ActiveCount);
        self::assertSame(1, $course2Count);
    }

    // 额外的 IS NULL 查询测试
    public function testFindByNullableFieldsAdditional(): void
    {
        $course = $this->createTestCourse(['title' => 'Test Course']);

        // 创建有分组的收藏
        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course);
        $collect1->setCollectGroup('favorites');
        $collect1->setNote('Great course');
        self::getEntityManager()->persist($collect1);

        // 创建无分组但有备注的收藏
        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course);
        $collect2->setCollectGroup(null);
        $collect2->setNote('Nice course');
        self::getEntityManager()->persist($collect2);

        // 创建既无分组也无备注的收藏
        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course);
        $collect3->setCollectGroup(null);
        $collect3->setNote(null);
        self::getEntityManager()->persist($collect3);

        // 创建有分组但无备注的收藏
        $collect4 = new Collect();
        $collect4->setUserId('user4');
        $collect4->setCourse($course);
        $collect4->setCollectGroup('learning');
        $collect4->setNote(null);
        self::getEntityManager()->persist($collect4);

        self::getEntityManager()->flush();

        // 测试查找无分组的收藏
        $collectsWithoutGroup = $this->repository->findBy(['collectGroup' => null]);
        self::assertCount(2, $collectsWithoutGroup);

        // 测试查找无备注的收藏
        $collectsWithoutNote = $this->repository->findBy(['note' => null]);
        self::assertCount(2, $collectsWithoutNote);

        // 测试查找既无分组也无备注的收藏
        $collectsWithoutGroupAndNote = $this->repository->findBy(['collectGroup' => null, 'note' => null]);
        self::assertCount(1, $collectsWithoutGroupAndNote);
        self::assertSame('user3', $collectsWithoutGroupAndNote[0]->getUserId());
    }

    // 额外的 count IS NULL 查询测试
    public function testCountByNullableFieldsAdditional(): void
    {
        $course = $this->createTestCourse(['title' => 'Test Course']);

        // 创建有分组的收藏
        $collect1 = new Collect();
        $collect1->setUserId('user1');
        $collect1->setCourse($course);
        $collect1->setCollectGroup('favorites');
        $collect1->setNote('Great course');
        self::getEntityManager()->persist($collect1);

        $collect2 = new Collect();
        $collect2->setUserId('user2');
        $collect2->setCourse($course);
        $collect2->setCollectGroup('learning');
        $collect2->setNote('Good course');
        self::getEntityManager()->persist($collect2);

        // 创建无分组的收藏
        $collect3 = new Collect();
        $collect3->setUserId('user3');
        $collect3->setCourse($course);
        $collect3->setCollectGroup(null);
        $collect3->setNote('Nice course');
        self::getEntityManager()->persist($collect3);

        $collect4 = new Collect();
        $collect4->setUserId('user4');
        $collect4->setCourse($course);
        $collect4->setCollectGroup(null);
        $collect4->setNote(null);
        self::getEntityManager()->persist($collect4);

        // 创建无备注的收藏
        $collect5 = new Collect();
        $collect5->setUserId('user5');
        $collect5->setCourse($course);
        $collect5->setCollectGroup('wishlist');
        $collect5->setNote(null);
        self::getEntityManager()->persist($collect5);

        self::getEntityManager()->flush();

        $countWithoutGroup = $this->repository->count(['collectGroup' => null]);
        $countWithoutNote = $this->repository->count(['note' => null]);
        $countWithGroup = $this->repository->count(['collectGroup' => 'favorites']);

        self::assertSame(2, $countWithoutGroup);
        self::assertSame(2, $countWithoutNote);
        self::assertSame(1, $countWithGroup);
    }

    protected function createNewEntity(): object
    {
        $entity = new Collect();
        $entity->setUserId('test_user_' . uniqid());
        $entity->setStatus('active');
        $entity->setSortNumber(0);
        $entity->setIsTop(false);

        // 创建必需的 Course 实体
        $course = $this->createTestCourse(['title' => 'Test Course ' . uniqid()]);
        $entity->setCourse($course);

        return $entity;
    }

    protected function getRepository(): CollectRepository
    {
        return $this->repository;
    }
}
