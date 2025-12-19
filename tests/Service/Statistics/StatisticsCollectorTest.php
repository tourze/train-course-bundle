<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsCollector;

/**
 * @internal
 */
#[CoversClass(StatisticsCollector::class)]
#[RunTestsInSeparateProcesses]
final class StatisticsCollectorTest extends AbstractIntegrationTestCase
{
    private StatisticsCollector $collector;

    protected function onSetUp(): void
    {
        // 获取真实的服务实例
        $this->collector = self::getService(StatisticsCollector::class);
    }

    public function testCollectStatisticsBasic(): void
    {
        $result = $this->collector->collectStatistics(false, 10);

        // Method signature guarantees array return
        $this->assertIsArray($result);
        $this->assertArrayHasKey('basic', $result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertArrayHasKey('engagement', $result);
        $this->assertArrayHasKey('audit', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayNotHasKey('detailed', $result);
    }

    public function testCollectStatisticsDetailed(): void
    {
        $result = $this->collector->collectStatistics(true, 5);

        // Method signature guarantees array return
        $this->assertIsArray($result);
        $this->assertArrayHasKey('basic', $result);
        $this->assertArrayHasKey('detailed', $result);
        $this->assertIsArray($result['detailed']);
        $this->assertArrayHasKey('top_courses', $result['detailed']);
        $this->assertArrayHasKey('category_stats', $result['detailed']);
        $this->assertArrayHasKey('monthly_trends', $result['detailed']);
    }

    public function testBasicStatisticsCalculation(): void
    {
        // 创建必需的分类和分类类型
        $catalogType = new CatalogType();
        $catalogType->setCode('test-course-type');
        $catalogType->setName('测试课程分类类型');

        $category = new Catalog();
        $category->setName('测试分类');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());

        $this->persistEntities([$catalogType, $category]);

        // 创建测试课程数据
        $courses = [];
        for ($i = 1; $i <= 5; $i++) {
            $course = new Course();
            $course->setTitle("测试课程{$i}");
            $course->setValid(true);
            $course->setSortNumber(100 - $i);
            $course->setCategory($category);
            $course->setLearnHour(10 + $i); // 设置必需的学习时长
            $course->setValidDay(180); // 设置有效天数
            $courses[] = $course;
        }

        // 创建一些无效课程
        for ($i = 1; $i <= 2; $i++) {
            $course = new Course();
            $course->setTitle("无效课程{$i}");
            $course->setValid(false);
            $course->setSortNumber(50 - $i);
            $course->setCategory($category);
            $course->setLearnHour(5 + $i); // 设置必需的学习时长
            $course->setValidDay(180); // 设置有效天数
            $courses[] = $course;
        }

        $this->persistEntities($courses);

        $result = $this->collector->collectStatistics(false, 10);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('basic', $result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertArrayHasKey('engagement', $result);
        $this->assertArrayHasKey('audit', $result);
        $this->assertArrayHasKey('version', $result);

        $this->assertIsArray($result['basic']);
        $this->assertArrayHasKey('total_courses', $result['basic']);
        $this->assertArrayHasKey('valid_courses', $result['basic']);
        $this->assertSame(20, $result['basic']['total_courses']); // 13个来自Fixtures + 7个测试创建
        $this->assertSame(18, $result['basic']['valid_courses']); // 13个Fixtures都是有效的 + 5个测试创建的有效课程
    }

    public function testBasicStatisticsWithZeroCourses(): void
    {
        // 注意：由于Fixtures会自动加载，这里实际上会有13个课程数据
        // 这个测试名称保持历史兼容，但期望值已更新

        $result = $this->collector->collectStatistics(false, 10);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('basic', $result);
        $this->assertIsArray($result['basic']);
        $this->assertSame(13, $result['basic']['total_courses']);
        $this->assertSame(13, $result['basic']['valid_courses']);
    }

    public function testTopCoursesRetrieval(): void
    {
        // 创建必需的分类和分类类型
        $catalogType = new CatalogType();
        $catalogType->setCode('test-top-courses-type');
        $catalogType->setName('热门课程分类类型');

        $category = new Catalog();
        $category->setName('热门课程分类');
        $category->setType($catalogType);
        $category->setSortOrder(1);
        $category->setCreateTime(new \DateTimeImmutable());
        $category->setUpdateTime(new \DateTimeImmutable());

        $this->persistEntities([$catalogType, $category]);

        // 创建测试课程数据
        $courses = [];
        for ($i = 1; $i <= 3; $i++) {
            $course = new Course();
            $course->setTitle("热门课程{$i}");
            $course->setValid(true);
            $course->setSortNumber(100 - $i * 10);
            $course->setCategory($category);
            $course->setLearnHour(20 + $i * 5); // 设置必需的学习时长
            $course->setValidDay(180); // 设置有效天数
            $courses[] = $course;
        }

        $this->persistEntities($courses);

        $result = $this->collector->collectStatistics(true, 5);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('detailed', $result);
        $this->assertIsArray($result['detailed']);
        $this->assertArrayHasKey('top_courses', $result['detailed']);
        $this->assertIsArray($result['detailed']['top_courses']);
    }
}
