<?php

namespace Tourze\TrainCourseBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;
use Tourze\TrainCourseBundle\Repository\CoursePlayControlRepository;

/**
 * @internal
 */
#[CoversClass(CoursePlayControlRepository::class)]
#[RunTestsInSeparateProcesses]
final class CoursePlayControlRepositoryTest extends AbstractRepositoryTestCase
{
    private CoursePlayControlRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CoursePlayControlRepository::class);
        self::assertInstanceOf(CoursePlayControlRepository::class, $this->repository);

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

            $control = new CoursePlayControl();
            $control->setCourse($course);
            $control->setEnabled(true);
            $control->setAllowFastForward(false);
            $control->setAllowSpeedControl(false);
            $control->setAllowSeeking(false);
            $control->setEnableWatermark(true);
            $control->setMaxDeviceCount(3);
            $control->setPlayAuthDuration(3600);
            self::getEntityManager()->persist($control);

            self::getEntityManager()->flush();
        }
    }

    public function testFindByCourse(): void
    {
        $course = $this->createTestCourse();
        $control = $this->createTestPlayControl($course);

        $found = $this->repository->findByCourse($course);

        self::assertInstanceOf(CoursePlayControl::class, $found);
        self::assertSame($control->getId(), $found->getId());
        $foundCourse = $found->getCourse();
        self::assertNotNull($foundCourse);
        self::assertSame($course->getId(), $foundCourse->getId());
    }

    public function testFindEnabledControls(): void
    {
        $courseEnabled = $this->createTestCourse();
        $controlEnabled = $this->createTestPlayControl($courseEnabled, ['enabled' => true]);

        $courseDisabled = $this->createTestCourse();
        $controlDisabled = $this->createTestPlayControl($courseDisabled, ['enabled' => false]);

        $results = $this->repository->findEnabledControls();

        self::assertIsArray($results);
        $ids = array_map(fn ($c) => $c->getId(), $results);
        self::assertContains($controlEnabled->getId(), $ids);
        self::assertNotContains($controlDisabled->getId(), $ids);
    }

    public function testFindWithFastForwardDisabled(): void
    {
        $course1 = $this->createTestCourse();
        $control1 = $this->createTestPlayControl($course1, [
            'enabled' => true,
            'allowFastForward' => false,
        ]);

        $course2 = $this->createTestCourse();
        $control2 = $this->createTestPlayControl($course2, [
            'enabled' => true,
            'allowFastForward' => true,
        ]);

        $results = $this->repository->findWithFastForwardDisabled();

        self::assertIsArray($results);
        $ids = array_map(fn ($c) => $c->getId(), $results);
        self::assertContains($control1->getId(), $ids);
        self::assertNotContains($control2->getId(), $ids);
    }

    public function testFindWithWatermarkEnabled(): void
    {
        $course1 = $this->createTestCourse();
        $control1 = $this->createTestPlayControl($course1, [
            'enabled' => true,
            'enableWatermark' => true,
        ]);

        $course2 = $this->createTestCourse();
        $control2 = $this->createTestPlayControl($course2, [
            'enabled' => true,
            'enableWatermark' => false,
        ]);

        $results = $this->repository->findWithWatermarkEnabled();

        self::assertIsArray($results);
        $ids = array_map(fn ($c) => $c->getId(), $results);
        self::assertContains($control1->getId(), $ids);
        self::assertNotContains($control2->getId(), $ids);
    }

    public function testFindStrictModeControls(): void
    {
        $course1 = $this->createTestCourse();
        $control1 = $this->createTestPlayControl($course1, [
            'enabled' => true,
            'allowFastForward' => false,
            'allowSeeking' => false,
        ]);

        $course2 = $this->createTestCourse();
        $control2 = $this->createTestPlayControl($course2, [
            'enabled' => true,
            'allowFastForward' => true,
            'allowSeeking' => true,
        ]);

        $results = $this->repository->findStrictModeControls();

        self::assertIsArray($results);
        $ids = array_map(fn ($c) => $c->getId(), $results);
        self::assertContains($control1->getId(), $ids);
        self::assertNotContains($control2->getId(), $ids);
    }

    public function testFindByMaxDeviceCount(): void
    {
        $course1 = $this->createTestCourse();
        $control1 = $this->createTestPlayControl($course1, [
            'enabled' => true,
            'maxDeviceCount' => 3,
        ]);

        $course2 = $this->createTestCourse();
        $control2 = $this->createTestPlayControl($course2, [
            'enabled' => true,
            'maxDeviceCount' => 5,
        ]);

        $results = $this->repository->findByMaxDeviceCount(3);

        self::assertIsArray($results);
        $ids = array_map(fn ($c) => $c->getId(), $results);
        self::assertContains($control1->getId(), $ids);
        self::assertNotContains($control2->getId(), $ids);
    }

    public function testGetPlayControlStatistics(): void
    {
        $course1 = $this->createTestCourse();
        $this->createTestPlayControl($course1, [
            'enabled' => true,
            'allowFastForward' => false,
            'enableWatermark' => true,
        ]);

        $course2 = $this->createTestCourse();
        $this->createTestPlayControl($course2, [
            'enabled' => false,
        ]);

        $stats = $this->repository->getPlayControlStatistics();

        // Method signature guarantees array return with known structure
        self::assertArrayHasKey('total_controls', $stats);
        self::assertArrayHasKey('enabled_controls', $stats);
        self::assertArrayHasKey('disabled_controls', $stats);
        self::assertArrayHasKey('fast_forward_disabled', $stats);
        self::assertArrayHasKey('watermark_enabled', $stats);
        self::assertArrayHasKey('strict_mode_count', $stats);

        self::assertGreaterThanOrEqual(2, $stats['total_controls']);
        self::assertGreaterThanOrEqual(1, $stats['enabled_controls']);
        self::assertGreaterThanOrEqual(1, $stats['disabled_controls']);
    }

    public function testFindNeedingAuthUpdate(): void
    {
        $course1 = $this->createTestCourse();
        $control1 = $this->createTestPlayControl($course1, [
            'enabled' => true,
            'playAuthDuration' => 600, // 10分钟，低于阈值
        ]);

        $course2 = $this->createTestCourse();
        $control2 = $this->createTestPlayControl($course2, [
            'enabled' => true,
            'playAuthDuration' => 3600, // 1小时，高于阈值
        ]);

        $results = $this->repository->findNeedingAuthUpdate(1800); // 30分钟阈值

        self::assertIsArray($results);
        $ids = array_map(fn ($c) => $c->getId(), $results);
        self::assertContains($control1->getId(), $ids);
        self::assertNotContains($control2->getId(), $ids);
    }

    public function testSave(): void
    {
        $course = $this->createTestCourse();
        $control = new CoursePlayControl();
        $control->setCourse($course);
        $control->setEnabled(true);

        $this->repository->save($control);

        $found = $this->repository->find($control->getId());
        self::assertNotNull($found);
        $foundCourse = $found->getCourse();
        self::assertNotNull($foundCourse);
        self::assertSame($course->getId(), $foundCourse->getId());
    }

    public function testRemove(): void
    {
        $control = $this->createTestPlayControl();
        $controlId = $control->getId();

        $this->repository->remove($control);

        $found = $this->repository->find($controlId);
        self::assertNull($found);
    }

    private function createTestCourse(): Course
    {
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
        $course->setTitle('Test Course ' . uniqid());
        $course->setDescription('Test Description');
        $course->setCategory($category);
        $course->setValid(true);
        $course->setValidDay(365);
        $course->setLearnHour(60);

        self::getEntityManager()->persist($course);
        self::getEntityManager()->flush();

        return $course;
    }

    /** @param array<string, mixed> $options */
    private function createTestPlayControl(?Course $course = null, array $options = []): CoursePlayControl
    {
        if (null === $course) {
            $course = $this->createTestCourse();
        }

        $control = new CoursePlayControl();
        $control->setCourse($course);
        $control->setEnabled((bool) ($options['enabled'] ?? true));
        $control->setAllowFastForward((bool) ($options['allowFastForward'] ?? false));
        $control->setAllowSpeedControl((bool) ($options['allowSpeedControl'] ?? false));
        $control->setEnableWatermark((bool) ($options['enableWatermark'] ?? true));

        $maxDeviceCount = $options['maxDeviceCount'] ?? 3;
        self::assertIsInt($maxDeviceCount, 'maxDeviceCount must be an integer');
        $control->setMaxDeviceCount($maxDeviceCount);

        $playAuthDuration = $options['playAuthDuration'] ?? 3600;
        self::assertIsInt($playAuthDuration, 'playAuthDuration must be an integer');
        $control->setPlayAuthDuration($playAuthDuration);

        $control->setAllowSeeking((bool) ($options['allowSeeking'] ?? false));

        self::getEntityManager()->persist($control);
        self::getEntityManager()->flush();

        return $control;
    }

    protected function createNewEntity(): object
    {
        $entity = new CoursePlayControl();
        $entity->setEnabled(true);
        $entity->setAllowFastForward(false);
        $entity->setAllowSpeedControl(false);
        $entity->setAllowSeeking(false);

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

    /** @return ServiceEntityRepository<CoursePlayControl> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
