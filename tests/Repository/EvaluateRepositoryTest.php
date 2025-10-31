<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;

/**
 * EvaluateRepository 集成测试
 *
 * @internal
 */
#[CoversClass(EvaluateRepository::class)]
#[RunTestsInSeparateProcesses]
final class EvaluateRepositoryTest extends AbstractRepositoryTestCase
{
    private EvaluateRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(EvaluateRepository::class);
        self::assertInstanceOf(EvaluateRepository::class, $this->repository);

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

            $evaluate = new Evaluate();
            $evaluate->setUserId('test_user_' . uniqid());
            $evaluate->setCourse($course);
            $evaluate->setRating(5);
            $evaluate->setContent('Test evaluation content');
            self::getEntityManager()->persist($evaluate);

            self::getEntityManager()->flush();
        }
    }

    public function testFindWithNonExistentId(): void
    {
        $found = $this->repository->find(99999);

        self::assertNull($found);
    }

    public function testFindAllWithEmptyDatabase(): void
    {
        $evaluates = $this->repository->findAll();

        self::assertIsArray($evaluates);
    }

    public function testFindByWithNonMatchingCriteria(): void
    {
        $course = $this->createTestCourse();
        $this->createEvaluateWithCourse($course, ['rating' => 5]);

        $evaluates = $this->repository->findBy(['rating' => 1, 'course' => $course]);

        self::assertIsArray($evaluates);
        self::assertEmpty($evaluates);
    }

    public function testFindOneByWithNonMatchingCriteria(): void
    {
        $this->createEvaluate(['content' => 'Test comment']);

        $found = $this->repository->findOneBy(['content' => 'NonExistent']);

        self::assertNull($found);
    }

    public function testFindOneByOrderingLogic(): void
    {
        $course = $this->createTestCourse();
        $this->createEvaluateWithCourse($course, ['rating' => 3, 'content' => 'Z Comment']);
        $this->createEvaluateWithCourse($course, ['rating' => 3, 'content' => 'A Comment']);
        $this->createEvaluateWithCourse($course, ['rating' => 3, 'content' => 'M Comment']);

        $firstByContent = $this->repository->findOneBy(['rating' => 3, 'course' => $course], ['content' => 'ASC']);
        $lastByContent = $this->repository->findOneBy(['rating' => 3, 'course' => $course], ['content' => 'DESC']);

        self::assertNotNull($firstByContent);
        self::assertSame('A Comment', $firstByContent->getContent());

        self::assertNotNull($lastByContent);
        self::assertSame('Z Comment', $lastByContent->getContent());
    }

    public function testFindByNullableField(): void
    {
        $course = $this->createTestCourse();
        $this->createEvaluateWithCourse($course, ['rating' => 5, 'userNickname' => 'John Doe']);
        $this->createEvaluateWithCourse($course, ['rating' => 3, 'userNickname' => null]);

        $withNickname = $this->repository->findBy(['userNickname' => 'John Doe', 'course' => $course]);
        $withoutNickname = $this->repository->findBy(['userNickname' => null, 'course' => $course]);

        self::assertCount(1, $withNickname);
        self::assertCount(1, $withoutNickname);
    }

    public function testCountByNullableField(): void
    {
        $course = $this->createTestCourse();
        $this->createEvaluateWithCourse($course, ['rating' => 5, 'userNickname' => 'John Doe']);
        $this->createEvaluateWithCourse($course, ['rating' => 3, 'userNickname' => null]);

        $countWithNickname = $this->repository->count(['userNickname' => 'John Doe', 'course' => $course]);
        $countWithoutNickname = $this->repository->count(['userNickname' => null, 'course' => $course]);

        self::assertSame(1, $countWithNickname);
        self::assertSame(1, $countWithoutNickname);
    }

    public function testFindByUserReturnsArray(): void
    {
        $evaluates = $this->repository->findByUser('1');
        self::assertIsArray($evaluates);
    }

    public function testFindByRatingReturnsArray(): void
    {
        $evaluates = $this->repository->findByRating(5);
        self::assertIsArray($evaluates);
    }

    public function testFindByStatusReturnsArray(): void
    {
        $evaluates = $this->repository->findByStatus('approved');
        self::assertIsArray($evaluates);
    }

    public function testGetEvaluateStatisticsReturnsArray(): void
    {
        $stats = $this->repository->getEvaluateStatistics();
        self::assertIsArray($stats);
    }

    public function testSearchEvaluatesReturnsArray(): void
    {
        $evaluates = $this->repository->searchEvaluates('test', null);
        self::assertIsArray($evaluates);
    }

    public function testFindPendingEvaluatesReturnsArray(): void
    {
        $evaluates = $this->repository->findPendingEvaluates();
        self::assertIsArray($evaluates);
    }

    // 添加缺失的测试用例以满足 PHPStan 要求

    // 测试关联查询
    public function testFindByAssociationField(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        $evaluate1 = $this->createEvaluateWithCourse($course1, ['rating' => 5, 'comment' => 'Great course 1']);
        $evaluate2 = $this->createEvaluateWithCourse($course1, ['rating' => 4, 'comment' => 'Good course 1']);
        $evaluate3 = $this->createEvaluateWithCourse($course2, ['rating' => 3, 'comment' => 'OK course 2']);

        // 通过课程查询评价
        $course1Evaluates = $this->repository->findBy(['course' => $course1]);
        $course2Evaluates = $this->repository->findBy(['course' => $course2]);

        self::assertCount(2, $course1Evaluates);
        self::assertCount(1, $course2Evaluates);

        // 验证评价归属正确
        foreach ($course1Evaluates as $evaluate) {
            $course = $evaluate->getCourse();
            self::assertNotNull($course);
            self::assertSame($course1->getId(), $course->getId());
        }

        foreach ($course2Evaluates as $evaluate) {
            $course = $evaluate->getCourse();
            self::assertNotNull($course);
            self::assertSame($course2->getId(), $course->getId());
        }
    }

    // 测试关联 count 查询
    public function testCountByAssociationField(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        // 为 Course 1 创建多个评价
        $this->createEvaluateWithCourse($course1, ['rating' => 5, 'status' => 'published']);
        $this->createEvaluateWithCourse($course1, ['rating' => 4, 'status' => 'published']);
        $this->createEvaluateWithCourse($course1, ['rating' => 3, 'status' => 'pending']);

        // 为 Course 2 创建评价
        $this->createEvaluateWithCourse($course2, ['rating' => 5, 'status' => 'published']);

        $course1Count = $this->repository->count(['course' => $course1]);
        $course1PublishedCount = $this->repository->count(['course' => $course1, 'status' => 'published']);
        $course2Count = $this->repository->count(['course' => $course2]);

        self::assertSame(3, $course1Count);
        self::assertSame(2, $course1PublishedCount);
        self::assertSame(1, $course2Count);
    }

    // 测试 IS NULL 查询
    public function testFindByNullableFieldsIsNull(): void
    {
        $course = $this->createTestCourse();

        // 创建有用户昵称的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 5,
            'comment' => 'Great!',
            'userNickname' => 'John Doe',
            'content' => 'Excellent course content',
        ]);

        // 创建无用户昵称但有内容的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 4,
            'comment' => 'Good!',
            'userNickname' => null,
            'content' => 'Good course',
        ]);

        // 创建既无昵称也无内容的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 3,
            'comment' => 'OK',
            'userNickname' => null,
            'content' => null,
        ]);

        // 创建有昵称但无内容的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 4,
            'comment' => 'Nice',
            'userNickname' => 'Jane Smith',
            'content' => null,
        ]);

        // 测试查找无用户昵称的评价
        $evaluatesWithoutNickname = $this->repository->findBy(['userNickname' => null, 'course' => $course]);
        self::assertCount(2, $evaluatesWithoutNickname);

        // 测试查找无内容的评价
        $evaluatesWithoutContent = $this->repository->findBy(['content' => null, 'course' => $course]);
        self::assertCount(2, $evaluatesWithoutContent);

        // 测试查找既无昵称也无内容的评价
        $evaluatesWithoutNicknameAndContent = $this->repository->findBy(['userNickname' => null, 'content' => null, 'course' => $course]);
        self::assertCount(1, $evaluatesWithoutNicknameAndContent);
    }

    // 测试 count IS NULL 查询
    public function testCountByNullableFieldsIsNull(): void
    {
        $course = $this->createTestCourse();

        // 创建有审核人员的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 5,
            'auditor' => 'admin1',
            'auditComment' => 'Good evaluation',
        ]);
        $this->createEvaluateWithCourse($course, [
            'rating' => 4,
            'auditor' => 'admin2',
            'auditComment' => 'OK evaluation',
        ]);

        // 创建无审核人员的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 3,
            'auditor' => null,
            'auditComment' => 'Pending review',
        ]);
        $this->createEvaluateWithCourse($course, [
            'rating' => 4,
            'auditor' => null,
            'auditComment' => null,
        ]);

        // 创建无审核意见的评价
        $this->createEvaluateWithCourse($course, [
            'rating' => 5,
            'auditor' => 'admin3',
            'auditComment' => null,
        ]);

        $countWithoutAuditor = $this->repository->count(['auditor' => null, 'course' => $course]);
        $countWithoutAuditComment = $this->repository->count(['auditComment' => null, 'course' => $course]);
        $countWithAuditor = $this->repository->count(['auditor' => 'admin1', 'course' => $course]);

        self::assertSame(2, $countWithoutAuditor);
        self::assertSame(2, $countWithoutAuditComment);
        self::assertSame(1, $countWithAuditor);
    }

    // 测试 findByCourse 方法
    public function testFindByCourse(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        $evaluate1 = $this->createEvaluateWithCourse($course1, ['rating' => 5]);
        $evaluate2 = $this->createEvaluateWithCourse($course1, ['rating' => 4]);
        $evaluate3 = $this->createEvaluateWithCourse($course2, ['rating' => 3]);

        $course1Evaluates = $this->repository->findByCourse($course1);

        self::assertIsArray($course1Evaluates);
        self::assertCount(2, $course1Evaluates);

        $foundEvaluateIds = array_map(fn ($evaluate) => $evaluate->getId(), $course1Evaluates);
        self::assertContains($evaluate1->getId(), $foundEvaluateIds);
        self::assertContains($evaluate2->getId(), $foundEvaluateIds);
        self::assertNotContains($evaluate3->getId(), $foundEvaluateIds);
    }

    // 测试 findByUserAndCourse 方法
    public function testFindByUserAndCourse(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        $evaluate1 = $this->createEvaluateWithCourse($course1, ['rating' => 5], 'user1');
        $evaluate2 = $this->createEvaluateWithCourse($course1, ['rating' => 4], 'user2');
        $evaluate3 = $this->createEvaluateWithCourse($course2, ['rating' => 3], 'user1');

        $foundEvaluate = $this->repository->findByUserAndCourse('user1', $course1);

        self::assertInstanceOf(Evaluate::class, $foundEvaluate);
        self::assertSame($evaluate1->getId(), $foundEvaluate->getId());
        self::assertSame('user1', $foundEvaluate->getUserId());
        $course = $foundEvaluate->getCourse();
        self::assertNotNull($course);
        self::assertSame($course1->getId(), $course->getId());

        // 测试不存在的组合
        $notFound = $this->repository->findByUserAndCourse('user3', $course1);
        self::assertNull($notFound);
    }

    // 测试 findLatestEvaluates 方法
    public function testFindLatestEvaluates(): void
    {
        $course = $this->createTestCourse();

        $this->createEvaluateWithCourse($course, ['rating' => 5, 'status' => 'published']);
        $this->createEvaluateWithCourse($course, ['rating' => 4, 'status' => 'published']);
        $this->createEvaluateWithCourse($course, ['rating' => 3, 'status' => 'pending']);

        $latestEvaluates = $this->repository->findLatestEvaluates($course, 10);

        self::assertIsArray($latestEvaluates);
        self::assertLessThanOrEqual(10, count($latestEvaluates));
        self::assertContainsOnlyInstancesOf(Evaluate::class, $latestEvaluates);
    }

    // 测试 findPopularEvaluates 方法
    public function testFindPopularEvaluates(): void
    {
        $course = $this->createTestCourse();

        $evaluate1 = $this->createEvaluateWithCourse($course, ['rating' => 5, 'status' => 'published']);
        $evaluate1->setLikeCount(100);

        $evaluate2 = $this->createEvaluateWithCourse($course, ['rating' => 4, 'status' => 'published']);
        $evaluate2->setLikeCount(50);

        self::getEntityManager()->flush();

        $popularEvaluates = $this->repository->findPopularEvaluates($course, 10);

        self::assertIsArray($popularEvaluates);
        self::assertLessThanOrEqual(10, count($popularEvaluates));
        self::assertContainsOnlyInstancesOf(Evaluate::class, $popularEvaluates);
    }

    // 测试 save 方法
    public function testSave(): void
    {
        $course = $this->createTestCourse();
        $evaluate = new Evaluate();
        $evaluate->setUserId('testuser');
        $evaluate->setCourse($course);
        $evaluate->setRating(5);
        $evaluate->setContent('Excellent course!');
        $evaluate->setStatus('published');

        $this->repository->save($evaluate, true);

        self::assertNotNull($evaluate->getId());

        $foundEvaluate = $this->repository->find($evaluate->getId());
        self::assertInstanceOf(Evaluate::class, $foundEvaluate);
        self::assertSame('Excellent course!', $foundEvaluate->getContent());
    }

    // 测试 save 方法不带 flush
    public function testSaveWithoutFlush(): void
    {
        $course = $this->createTestCourse();
        $evaluate = new Evaluate();
        $evaluate->setUserId('testuser2');
        $evaluate->setCourse($course);
        $evaluate->setRating(4);
        $evaluate->setContent('Good course!');
        $evaluate->setStatus('published');

        $this->repository->save($evaluate, false);

        // 使用 SnowflakeKeyAware 时，persist 会立即生成 ID
        self::assertNotNull($evaluate->getId());
        $originalId = $evaluate->getId();

        // 手动 flush
        self::getEntityManager()->flush();

        self::assertSame($originalId, $evaluate->getId());
    }

    // 测试 remove 方法
    public function testRemove(): void
    {
        $course = $this->createTestCourse();
        $evaluate = $this->createEvaluateWithCourse($course, ['rating' => 5]);

        $evaluateId = $evaluate->getId();

        $this->repository->remove($evaluate, true);

        $foundEvaluate = $this->repository->find($evaluateId);
        self::assertNull($foundEvaluate);
    }

    // 测试 remove 方法不带 flush

    /** @param array<string, mixed> $data */
    private function createTestCourse(array $data = []): Course
    {
        $category = $this->createTestCategory();
        $course = new Course();

        $this->setCourseBasicProperties($course, $data);
        $course->setCategory($category);
        $this->setCourseAdvancedProperties($course, $data);

        self::getEntityManager()->persist($course);
        self::getEntityManager()->flush();

        return $course;
    }

    private function createTestCategory(): Catalog
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test_course_category_' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

        return $category;
    }

    /** @param array<string, mixed> $data */
    private function setCourseBasicProperties(Course $course, array $data): void
    {
        if (isset($data['title']) && is_string($data['title'])) {
            $course->setTitle($data['title']);
        } else {
            $course->setTitle('Test Course ' . uniqid());
        }

        $this->setNullableStringProperty($data, 'description', 'Test Description', fn ($v) => $course->setDescription($v));
        $this->setNullableStringProperty($data, 'price', '100.0', fn ($v) => $course->setPrice($v));
        $this->setNullableBoolProperty($data, 'valid', true, fn ($v) => $course->setValid($v));
    }

    /** @param array<string, mixed> $data */
    private function setCourseAdvancedProperties(Course $course, array $data): void
    {
        $course->setValidDay((isset($data['validDay']) && is_int($data['validDay'])) ? $data['validDay'] : 365);
        $course->setLearnHour((isset($data['learnHour']) && is_int($data['learnHour'])) ? $data['learnHour'] : 40);
        $course->setSortNumber((isset($data['sortNumber']) && is_int($data['sortNumber'])) ? $data['sortNumber'] : 1);
    }

    /**
     * @param array<string, mixed> $data
     * @param callable(string|null): void $setter
     */
    private function setNullableStringProperty(array $data, string $key, string $default, callable $setter): void
    {
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
            if (is_string($value) || null === $value) {
                $setter($value);
            }
        } else {
            $setter($default);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param callable(bool|null): void $setter
     */
    private function setNullableBoolProperty(array $data, string $key, bool $default, callable $setter): void
    {
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
            if (is_bool($value) || null === $value) {
                $setter($value);
            }
        } else {
            $setter($default);
        }
    }

    /** @param array<string, mixed> $attributes */
    private function createEvaluateWithCourse(Course $course, array $attributes = [], ?string $userId = null): Evaluate
    {
        $evaluate = new Evaluate();
        $evaluate->setUserId($userId ?? 'user' . uniqid());
        $evaluate->setCourse($course);

        $this->setEvaluateProperties($evaluate, $attributes);

        self::getEntityManager()->persist($evaluate);
        self::getEntityManager()->flush();

        return $evaluate;
    }

    /** @param array<string, mixed> $attributes */
    private function setEvaluateProperties(Evaluate $evaluate, array $attributes): void
    {
        $evaluate->setRating((isset($attributes['rating']) && is_int($attributes['rating'])) ? $attributes['rating'] : 5);

        $this->setNullableStringProperty($attributes, 'content', 'Test evaluate comment', fn ($v) => $evaluate->setContent($v));

        if (isset($attributes['status']) && is_string($attributes['status'])) {
            $evaluate->setStatus($attributes['status']);
        } else {
            $evaluate->setStatus('published');
        }

        $this->setOptionalNullableString($attributes, 'userNickname', fn ($v) => $evaluate->setUserNickname($v));
        $this->setOptionalNullableString($attributes, 'auditor', fn ($v) => $evaluate->setAuditor($v));
        $this->setOptionalNullableString($attributes, 'auditComment', fn ($v) => $evaluate->setAuditComment($v));
    }

    /**
     * @param array<string, mixed> $data
     * @param callable(string|null): void $setter
     */
    private function setOptionalNullableString(array $data, string $key, callable $setter): void
    {
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
            if (is_string($value) || null === $value) {
                $setter($value);
            }
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createEvaluate(array $attributes = []): Evaluate
    {
        $course = $this->createTestCourse();

        return $this->createEvaluateWithCourse($course, $attributes);
    }

    protected function createNewEntity(): object
    {
        $entity = new Evaluate();
        $entity->setUserId('test_user_' . uniqid());
        $entity->setRating(5);
        $entity->setContent('Test evaluation content');

        // 创建必需的 Course 实体
        $catalogType = new CatalogType();
        $catalogType->setCode('test_course_category_' . uniqid());
        $catalogType->setName('课程分类');
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setName('Test Category');
        $category->setSortOrder(1);
        $category->setType($catalogType);
        self::getEntityManager()->persist($category);

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

    /** @return ServiceEntityRepository<Evaluate> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
