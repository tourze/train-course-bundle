<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;

/**
 * CourseAuditRepository 集成测试
 *
 * @internal
 */
#[CoversClass(CourseAuditRepository::class)]
#[RunTestsInSeparateProcesses]
final class CourseAuditRepositoryTest extends AbstractRepositoryTestCase
{
    private CourseAuditRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CourseAuditRepository::class);
        self::assertInstanceOf(CourseAuditRepository::class, $this->repository);

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

            $audit = new CourseAudit();
            $audit->setCourse($course);
            $audit->setStatus('pending');
            $audit->setAuditType('content');
            $audit->setAuditLevel(1);
            $audit->setPriority(0);
            self::getEntityManager()->persist($audit);

            self::getEntityManager()->flush();
        }
    }

    public function testFind(): void
    {
        $course = $this->createTestCourse();
        $audit = $this->createTestAudit($course);

        $found = $this->repository->find($audit->getId());
        self::assertInstanceOf(CourseAudit::class, $found);
        self::assertSame($audit->getId(), $found->getId());
    }

    public function testFindAll(): void
    {
        $course = $this->createTestCourse();
        $this->createTestAudit($course, 'pending');
        $this->createTestAudit($course, 'approved');

        $audits = $this->repository->findAll();
        self::assertIsArray($audits);
        self::assertGreaterThanOrEqual(2, count($audits));
        self::assertContainsOnlyInstancesOf(CourseAudit::class, $audits);
    }

    public function testFindByWithNonMatchingCriteria(): void
    {
        $course = $this->createTestCourse();
        $this->createTestAudit($course, 'pending');

        $results = $this->repository->findBy(['status' => 'NonExistent']);

        self::assertIsArray($results);
        self::assertEmpty($results);
    }

    public function testFindBy(): void
    {
        $course = $this->createTestCourse();
        $audit1 = $this->createTestAudit($course, 'pending');
        $audit2 = $this->createTestAudit($course, 'approved');

        $pendingAudits = $this->repository->findBy(['status' => 'pending']);
        self::assertIsArray($pendingAudits);
        self::assertContains($audit1, $pendingAudits);
        self::assertNotContains($audit2, $pendingAudits);
    }

    public function testFindOneByWithNonMatchingCriteria(): void
    {
        $course = $this->createTestCourse();
        $this->createTestAudit($course, 'pending', 'content');

        $found = $this->repository->findOneBy(['auditType' => 'NonExistent']);

        self::assertNull($found);
    }

    public function testFindOneBy(): void
    {
        $course = $this->createTestCourse();
        $audit = $this->createTestAudit($course, 'pending', 'content');

        $found = $this->repository->findOneBy(['auditType' => 'content', 'course' => $course]);
        self::assertInstanceOf(CourseAudit::class, $found);
        self::assertSame($audit->getId(), $found->getId());
    }

    public function testFindOneByReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findOneBy(['status' => 'nonexistent']);
        self::assertNull($found);
    }

    public function testCount(): void
    {
        $course = $this->createTestCourse();
        $initialCount = $this->repository->count([]);

        $this->createTestAudit($course, 'pending');
        $this->createTestAudit($course, 'approved');

        $finalCount = $this->repository->count([]);
        self::assertSame($initialCount + 2, $finalCount);
    }

    public function testCountWithCriteria(): void
    {
        $course = $this->createTestCourse();
        $this->createTestAudit($course, 'pending');
        $this->createTestAudit($course, 'approved');

        $pendingCount = $this->repository->count(['status' => 'pending']);
        $approvedCount = $this->repository->count(['status' => 'approved']);

        self::assertGreaterThanOrEqual(1, $pendingCount);
        self::assertGreaterThanOrEqual(1, $approvedCount);
    }

    public function testFindByCourse(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $audit1 = $this->createTestAudit($course1, 'pending');
        $audit2 = $this->createTestAudit($course1, 'approved');
        $this->createTestAudit($course2, 'pending');

        $courseAudits = $this->repository->findByCourse($course1);
        self::assertIsArray($courseAudits);
        self::assertCount(2, $courseAudits);
        self::assertContains($audit1, $courseAudits);
        self::assertContains($audit2, $courseAudits);
    }

    public function testFindByStatus(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $audit1 = $this->createTestAudit($course1, 'pending');
        $audit2 = $this->createTestAudit($course2, 'pending');
        $this->createTestAudit($course1, 'approved');

        $pendingAudits = $this->repository->findByStatus('pending');
        self::assertIsArray($pendingAudits);
        self::assertGreaterThanOrEqual(2, count($pendingAudits));
        self::assertContains($audit1, $pendingAudits);
        self::assertContains($audit2, $pendingAudits);
    }

    public function testFindPendingAudits(): void
    {
        $course = $this->createTestCourse();
        $audit1 = $this->createTestAudit($course, 'pending');
        $this->createTestAudit($course, 'approved');

        $pendingAudits = $this->repository->findPendingAudits();
        self::assertIsArray($pendingAudits);
        self::assertContains($audit1, $pendingAudits);
    }

    public function testFindOverdueAudits(): void
    {
        $audits = $this->repository->findOverdueAudits();
        self::assertIsArray($audits);
    }

    public function testFindByAuditor(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $audit1 = $this->createTestAudit($course1, 'pending', 'content', 'auditor1');
        $audit2 = $this->createTestAudit($course2, 'pending', 'content', 'auditor1');
        $this->createTestAudit($course1, 'pending', 'content', 'auditor2');

        $auditorAudits = $this->repository->findByAuditor('auditor1');
        self::assertIsArray($auditorAudits);
        self::assertGreaterThanOrEqual(2, count($auditorAudits));
        self::assertContains($audit1, $auditorAudits);
        self::assertContains($audit2, $auditorAudits);
    }

    public function testFindByAuditType(): void
    {
        $course1 = $this->createTestCourse();
        $course2 = $this->createTestCourse();
        $audit1 = $this->createTestAudit($course1, 'pending', 'content');
        $audit2 = $this->createTestAudit($course2, 'pending', 'content');
        $this->createTestAudit($course1, 'pending', 'quality');

        $contentAudits = $this->repository->findByAuditType('content');
        self::assertIsArray($contentAudits);
        self::assertGreaterThanOrEqual(2, count($contentAudits));
        self::assertContains($audit1, $contentAudits);
        self::assertContains($audit2, $contentAudits);
    }

    public function testGetAuditStatistics(): void
    {
        $course = $this->createTestCourse();
        $this->createTestAudit($course, 'pending');
        $this->createTestAudit($course, 'approved');
        $this->createTestAudit($course, 'rejected');

        $stats = $this->repository->getAuditStatistics();
        // Method signature guarantees array return with known structure
        self::assertArrayHasKey('total_audits', $stats);
        self::assertArrayHasKey('pending_audits', $stats);
        self::assertArrayHasKey('approved_audits', $stats);
        self::assertArrayHasKey('rejected_audits', $stats);
        self::assertGreaterThanOrEqual(3, $stats['total_audits']);
    }

    public function testFindLatestByCourse(): void
    {
        $course = $this->createTestCourse();

        // 创建第一个审核记录，并设置特定的时间
        $firstAudit = $this->createTestAudit($course, 'approved');
        $firstAudit->setCreateTime(new \DateTimeImmutable('-1 hour'));

        // 创建第二个审核记录，设置更新的时间
        $latestAudit = $this->createTestAudit($course, 'pending');
        $latestAudit->setCreateTime(new \DateTimeImmutable());

        self::getEntityManager()->flush();

        $found = $this->repository->findLatestByCourse($course);
        self::assertInstanceOf(CourseAudit::class, $found);
        self::assertSame($latestAudit->getId(), $found->getId());
    }

    public function testFindOldAudits(): void
    {
        $days = 30;
        $audits = $this->repository->findOldAudits($days);
        self::assertIsArray($audits);
    }

    public function testFindTimeoutAudits(): void
    {
        $hours = 24;
        $audits = $this->repository->findTimeoutAudits($hours);
        self::assertIsArray($audits);
    }

    public function testSave(): void
    {
        $course = $this->createTestCourse();
        $audit = new CourseAudit();
        $audit->setCourse($course);
        $audit->setStatus('pending');
        $audit->setAuditType('content');

        $this->repository->save($audit);

        $found = $this->repository->find($audit->getId());
        self::assertInstanceOf(CourseAudit::class, $found);
        self::assertSame('pending', $found->getStatus());
    }

    public function testSaveWithoutFlush(): void
    {
        $course = $this->createTestCourse();
        $audit = new CourseAudit();
        $audit->setCourse($course);
        $audit->setStatus('approved');
        $audit->setAuditType('quality');

        $this->repository->save($audit, false);
        self::getEntityManager()->flush();

        $found = $this->repository->find($audit->getId());
        self::assertInstanceOf(CourseAudit::class, $found);
    }

    public function testRemove(): void
    {
        $course = $this->createTestCourse();
        $audit = $this->createTestAudit($course);
        $auditId = $audit->getId();

        $this->repository->remove($audit);

        $found = $this->repository->find($auditId);
        self::assertNull($found);
    }

    public function testFindByNullableField(): void
    {
        $course = $this->createTestCourse();
        $this->createTestAudit($course, 'pending', 'content', 'auditor1');
        $this->createTestAudit($course, 'pending', 'content');

        $withAuditor = $this->repository->findBy(['auditor' => 'auditor1']);
        $withoutAuditor = $this->repository->findBy(['auditor' => null]);

        self::assertIsArray($withAuditor);
        self::assertIsArray($withoutAuditor);
        self::assertGreaterThanOrEqual(1, count($withAuditor));
        self::assertGreaterThanOrEqual(1, count($withoutAuditor));
    }

    /** @param array<string, mixed> $data */
    private function createTestCourse(array $data = []): Course
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
        $course->setTitle(\is_string($data['title'] ?? null) ? $data['title'] : 'Test Course ' . uniqid());
        $course->setDescription(\is_string($data['description'] ?? null) ? $data['description'] : 'Test Description');
        $course->setPrice(\is_string($data['price'] ?? null) ? $data['price'] : '100.0');
        $course->setCategory($category);
        $course->setValid(\is_bool($data['valid'] ?? null) ? $data['valid'] : true);
        $course->setValidDay(\is_int($data['validDay'] ?? null) ? $data['validDay'] : 365);
        $course->setLearnHour(\is_int($data['learnHour'] ?? null) ? $data['learnHour'] : 40);
        $course->setSortNumber(\is_int($data['sortNumber'] ?? null) ? $data['sortNumber'] : 1);

        self::getEntityManager()->persist($course);
        self::getEntityManager()->flush();

        return $course;
    }

    private function createTestAudit(
        Course $course,
        string $status = 'pending',
        string $auditType = 'content',
        ?string $auditor = null,
    ): CourseAudit {
        $audit = new CourseAudit();
        $audit->setCourse($course);
        $audit->setStatus($status);
        $audit->setAuditType($auditType);
        if (null !== $auditor) {
            $audit->setAuditor($auditor);
        }

        self::getEntityManager()->persist($audit);
        self::getEntityManager()->flush();

        return $audit;
    }

    // 添加缺失的测试用例以满足 PHPStan 要求

    public function testFindOneByOrderingLogic(): void
    {
        $course = $this->createTestCourse();

        $audit1 = $this->createTestAudit($course, 'pending', 'content', 'auditor1');
        $audit2 = $this->createTestAudit($course, 'pending', 'content', 'auditor2');
        $audit3 = $this->createTestAudit($course, 'pending', 'content', 'auditor3');

        // 设置不同的审核级别用于排序测试
        $audit1->setAuditLevel(3);
        $audit2->setAuditLevel(1);
        $audit3->setAuditLevel(2);
        self::getEntityManager()->flush();

        // 测试按审核级别 ASC 排序获取第一个
        $firstByLevelAsc = $this->repository->findOneBy(['status' => 'pending', 'course' => $course], ['auditLevel' => 'ASC']);
        self::assertInstanceOf(CourseAudit::class, $firstByLevelAsc);
        self::assertSame($audit2->getId(), $firstByLevelAsc->getId()); // auditLevel = 1

        // 测试按审核级别 DESC 排序获取第一个
        $firstByLevelDesc = $this->repository->findOneBy(['status' => 'pending', 'course' => $course], ['auditLevel' => 'DESC']);
        self::assertInstanceOf(CourseAudit::class, $firstByLevelDesc);
        self::assertSame($audit1->getId(), $firstByLevelDesc->getId()); // auditLevel = 3

        // 测试按审核人排序
        $firstByAuditor = $this->repository->findOneBy(['status' => 'pending', 'course' => $course], ['auditor' => 'ASC']);
        self::assertInstanceOf(CourseAudit::class, $firstByAuditor);
        self::assertSame('auditor1', $firstByAuditor->getAuditor());
    }

    // 测试关联查询
    public function testFindByAssociationField(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        $audit1 = $this->createTestAudit($course1, 'pending', 'content');
        $audit2 = $this->createTestAudit($course1, 'approved', 'quality');
        $audit3 = $this->createTestAudit($course2, 'pending', 'content');

        // 通过课程查询审核记录
        $course1Audits = $this->repository->findBy(['course' => $course1]);
        $course2Audits = $this->repository->findBy(['course' => $course2]);

        self::assertCount(2, $course1Audits);
        self::assertCount(1, $course2Audits);

        // 验证审核记录归属正确
        foreach ($course1Audits as $audit) {
            $course = $audit->getCourse();
            self::assertNotNull($course);
            self::assertSame($course1->getId(), $course->getId());
        }

        foreach ($course2Audits as $audit) {
            $course = $audit->getCourse();
            self::assertNotNull($course);
            self::assertSame($course2->getId(), $course->getId());
        }
    }

    // 测试关联 count 查询
    public function testCountByAssociationField(): void
    {
        $course1 = $this->createTestCourse(['title' => 'Course 1']);
        $course2 = $this->createTestCourse(['title' => 'Course 2']);

        // 为 Course 1 创建多个审核记录
        $this->createTestAudit($course1, 'pending', 'content');
        $this->createTestAudit($course1, 'approved', 'quality');
        $this->createTestAudit($course1, 'rejected', 'final');

        // 为 Course 2 创建审核记录
        $this->createTestAudit($course2, 'pending', 'content');

        $course1Count = $this->repository->count(['course' => $course1]);
        $course2Count = $this->repository->count(['course' => $course2]);
        $course1PendingCount = $this->repository->count(['course' => $course1, 'status' => 'pending']);
        $course2PendingCount = $this->repository->count(['course' => $course2, 'status' => 'pending']);

        self::assertSame(3, $course1Count);
        self::assertSame(1, $course2Count);
        self::assertSame(1, $course1PendingCount);
        self::assertSame(1, $course2PendingCount);
    }

    // 测试 IS NULL 查询
    public function testFindByNullableFieldsIsNull(): void
    {
        $course = $this->createTestCourse();

        // 创建有审核人的记录
        $this->createTestAudit($course, 'pending', 'content', 'auditor1');
        $this->createTestAudit($course, 'approved', 'quality', 'auditor2');

        // 创建无审核人的记录
        $this->createTestAudit($course, 'pending', 'content', null);
        $this->createTestAudit($course, 'rejected', 'final', null);

        // 创建有审核意见的记录
        $auditWithComment = $this->createTestAudit($course, 'approved', 'content', 'auditor3');
        $auditWithComment->setAuditComment('Good content');
        self::getEntityManager()->flush();

        // 创建无审核意见的记录
        $auditWithoutComment = $this->createTestAudit($course, 'pending', 'quality', 'auditor4');
        $auditWithoutComment->setAuditComment(null);
        self::getEntityManager()->flush();

        // 测试查找无审核人的记录
        $auditsWithoutAuditor = $this->repository->findBy(['auditor' => null]);
        self::assertCount(2, $auditsWithoutAuditor);

        // 测试查找无审核意见的记录
        $auditsWithoutComment = $this->repository->findBy(['auditComment' => null]);
        self::assertGreaterThanOrEqual(1, count($auditsWithoutComment));
        // 验证我们创建的记录在结果中
        $found = false;
        foreach ($auditsWithoutComment as $audit) {
            if ($audit->getId() === $auditWithoutComment->getId()) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found, '创建的无审核意见记录应该在结果中');

        // 测试查找有审核人的记录
        $auditsWithAuditor = $this->repository->findBy(['auditor' => 'auditor1']);
        self::assertCount(1, $auditsWithAuditor);
        self::assertSame('auditor1', $auditsWithAuditor[0]->getAuditor());
    }

    // 测试 count IS NULL 查询
    public function testCountByNullableFieldsIsNull(): void
    {
        $course = $this->createTestCourse();

        // 创建有审核人的记录
        $this->createTestAudit($course, 'pending', 'content', 'auditor1');
        $this->createTestAudit($course, 'approved', 'quality', 'auditor2');

        // 创建无审核人的记录
        $this->createTestAudit($course, 'pending', 'content', null);
        $this->createTestAudit($course, 'rejected', 'final', null);

        // 创建有截止时间的记录
        $auditWithDeadline = $this->createTestAudit($course, 'pending', 'content', 'auditor3');
        $auditWithDeadline->setDeadline(new \DateTimeImmutable('+1 week'));
        self::getEntityManager()->flush();

        // 创建无截止时间的记录
        $auditWithoutDeadline = $this->createTestAudit($course, 'pending', 'quality', 'auditor4');
        $auditWithoutDeadline->setDeadline(null);
        self::getEntityManager()->flush();

        $countWithoutAuditor = $this->repository->count(['auditor' => null]);
        $countWithAuditor = $this->repository->count(['auditor' => 'auditor1']);
        $countWithoutDeadline = $this->repository->count(['deadline' => null]);

        self::assertSame(2, $countWithoutAuditor);
        self::assertSame(1, $countWithAuditor);
        self::assertGreaterThanOrEqual(1, $countWithoutDeadline);
    }

    protected function createNewEntity(): object
    {
        $entity = new CourseAudit();
        $entity->setStatus('pending');
        $entity->setAuditType('content');
        $entity->setAuditLevel(1);
        $entity->setPriority(0);

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

    /** @return ServiceEntityRepository<CourseAudit> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
