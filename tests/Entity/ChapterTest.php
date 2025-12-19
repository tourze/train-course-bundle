<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * Chapter 实体测试
 *
 * 测试章节实体的基础属性、关联关系和业务方法
 *
 * @internal
 */
#[CoversClass(Chapter::class)]
final class ChapterTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Chapter();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'title' => ['title', 'test_value'],
        ];
    }

    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->chapter = new Chapter();
    }

    public function testConstructInitializesLessonsCollection(): void
    {
        $chapter = new Chapter();

        $this->assertCount(0, $chapter->getLessons());
    }

    public function testToStringReturnsEmptyStringWhenNoId(): void
    {
        $chapter = new Chapter();

        $this->assertSame('', (string) $chapter);
    }

    public function testToStringReturnsTitleWhenHasId(): void
    {
        $chapter = new Chapter();
        $chapter->setTitle('第一章 安全基础');

        // 模拟有ID的情况
        $reflection = new \ReflectionClass($chapter);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($chapter, '123456789');

        $this->assertSame('第一章 安全基础', (string) $chapter);
    }

    public function testCreatedByProperty(): void
    {
        $this->assertNull($this->chapter->getCreatedBy());

        $this->chapter->setCreatedBy('user123');
        $this->assertSame('user123', $this->chapter->getCreatedBy());

        $this->chapter->setCreatedBy(null);
        $this->assertNull($this->chapter->getCreatedBy());
    }

    public function testUpdatedByProperty(): void
    {
        $this->assertNull($this->chapter->getUpdatedBy());

        $this->chapter->setUpdatedBy('user456');
        $this->assertSame('user456', $this->chapter->getUpdatedBy());

        $this->chapter->setUpdatedBy(null);
        $this->assertNull($this->chapter->getUpdatedBy());
    }

    public function testCourseProperty(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：Chapter实体与Course实体存在多对一关联关系，需要测试setCourse和getCourse方法
         * 必要性：测试关联属性的设置和获取功能，验证对象引用的正确性
         * 替代方案：可以使用真实的Course实体对象，但Mock更适合单元测试的隔离性原则
         */
        $course = $this->createMock(Course::class);

        $this->chapter->setCourse($course);
        $this->assertSame($course, $this->chapter->getCourse());
    }

    public function testTitleProperty(): void
    {
        $this->chapter->setTitle('第一章 安全基础知识');
        $this->assertSame('第一章 安全基础知识', $this->chapter->getTitle());
    }

    public function testLessonManagement(): void
    {
        /*
         * 使用具体的Lesson实体类创建Mock对象
         * 原因：Chapter实体与Lesson实体存在一对多关联关系，需要测试课时集合的管理功能
         * 必要性：测试addLesson、removeLesson等集合操作方法，验证课时关联的增删功能
         * 替代方案：可以使用真实的Lesson实体对象，但Mock能避免复杂的实体初始化
         */
        $lesson1 = $this->createMock(Lesson::class);
        /* 使用具体的Lesson实体类创建第二个Mock对象，与lesson1相同的使用原因 */
        $lesson2 = $this->createMock(Lesson::class);

        // 测试添加课时
        $this->chapter->addLesson($lesson1);
        $this->assertCount(1, $this->chapter->getLessons());
        $this->assertTrue($this->chapter->getLessons()->contains($lesson1));

        $this->chapter->addLesson($lesson2);
        $this->assertCount(2, $this->chapter->getLessons());

        // 测试重复添加同一课时
        $this->chapter->addLesson($lesson1);
        $this->assertCount(2, $this->chapter->getLessons());

        // 测试移除课时
        $this->chapter->removeLesson($lesson1);
        $this->assertCount(1, $this->chapter->getLessons());
        $this->assertFalse($this->chapter->getLessons()->contains($lesson1));

        // 测试移除不存在的课时
        $this->chapter->removeLesson($lesson1);
        $this->assertCount(1, $this->chapter->getLessons());
    }

    public function testLessonCountWithEmptyLessons(): void
    {
        $this->assertSame(0, $this->chapter->getLessonCount());
    }

    public function testLessonCountWithValidLessons(): void
    {
        /*
         * 使用具体的Lesson实体类创建Mock对象
         * 原因：测试getLessonCount方法需要调用Lesson实体的getVideoUrl方法来判断课时有效性
         * 必要性：需要Mock Lesson的getVideoUrl方法返回不同值，测试课时统计的业务逻辑
         * 替代方案：使用真实Lesson对象会增加测试复杂度，Mock更适合验证特定业务规则
         */
        $lesson1 = $this->createMock(Lesson::class);
        $lesson1->method('getVideoUrl')->willReturn('ali://video1');

        /* 使用具体的Lesson实体类创建第二个Mock对象，与lesson1相同的使用原因 */
        $lesson2 = $this->createMock(Lesson::class);
        $lesson2->method('getVideoUrl')->willReturn('ali://video2');

        /* 使用具体的Lesson实体类创建第三个Mock对象，与上述相同的使用原因 */
        $lesson3 = $this->createMock(Lesson::class);
        $lesson3->method('getVideoUrl')->willReturn(null); // 无效课时

        $this->chapter->addLesson($lesson1);
        $this->chapter->addLesson($lesson2);
        $this->chapter->addLesson($lesson3);

        $this->assertSame(2, $this->chapter->getLessonCount()); // 只统计有视频URL的课时
    }

    public function testLessonTimeWithEmptyLessons(): void
    {
        $this->assertSame(0.0, $this->chapter->getLessonTime());
    }

    public function testLessonTimeWithValidLessons(): void
    {
        /*
         * 使用具体的Lesson实体类创建Mock对象
         * 原因：测试getLessonTime方法需要调用Lesson实体的getVideoUrl和getLessonTime方法
         * 必要性：需要Mock多个Lesson方法来模拟不同的课时状态和时长，验证总时长计算逻辑
         * 替代方案：使用真实Lesson对象需要复杂的数据设置，Mock能精确控制方法返回值
         */
        $lesson1 = $this->createMock(Lesson::class);
        $lesson1->method('getVideoUrl')->willReturn('ali://video1');
        $lesson1->method('getLessonTime')->willReturn(2.5);

        /* 使用具体的Lesson实体类创建第二个Mock对象，与lesson1相同的使用原因 */
        $lesson2 = $this->createMock(Lesson::class);
        $lesson2->method('getVideoUrl')->willReturn('ali://video2');
        $lesson2->method('getLessonTime')->willReturn(3.0);

        /* 使用具体的Lesson实体类创建第三个Mock对象，与上述相同的使用原因 */
        $lesson3 = $this->createMock(Lesson::class);
        $lesson3->method('getVideoUrl')->willReturn(null); // 无效课时
        $lesson3->method('getLessonTime')->willReturn(1.5);

        $this->chapter->addLesson($lesson1);
        $this->chapter->addLesson($lesson2);
        $this->chapter->addLesson($lesson3);

        $this->assertSame(5.5, $this->chapter->getLessonTime()); // 只统计有视频URL的课时
    }

    public function testDurationSecondWithEmptyLessons(): void
    {
        $this->assertSame(0, $this->chapter->getDurationSecond());
    }

    public function testDurationSecondWithValidLessons(): void
    {
        /*
         * 使用具体的Lesson实体类创建Mock对象
         * 原因：测试getDurationSecond方法需要调用Lesson实体的getVideoUrl和getDurationSecond方法
         * 必要性：需要Mock多个Lesson方法来验证总时长计算，测试只统计有效课时的业务逻辑
         * 替代方案：真实对象需要设置复杂的属性，Mock能简化测试并专注于业务逻辑验证
         */
        $lesson1 = $this->createMock(Lesson::class);
        $lesson1->method('getVideoUrl')->willReturn('ali://video1');
        $lesson1->method('getDurationSecond')->willReturn(1800); // 30分钟

        /* 使用具体的Lesson实体类创建第二个Mock对象，与lesson1相同的使用原因 */
        $lesson2 = $this->createMock(Lesson::class);
        $lesson2->method('getVideoUrl')->willReturn('ali://video2');
        $lesson2->method('getDurationSecond')->willReturn(2400); // 40分钟

        /* 使用具体的Lesson实体类创建第三个Mock对象，与上述相同的使用原因 */
        $lesson3 = $this->createMock(Lesson::class);
        $lesson3->method('getVideoUrl')->willReturn(null); // 无效课时
        $lesson3->method('getDurationSecond')->willReturn(600);

        $this->chapter->addLesson($lesson1);
        $this->chapter->addLesson($lesson2);
        $this->chapter->addLesson($lesson3);

        $this->assertSame(4200, $this->chapter->getDurationSecond()); // 只统计有视频URL的课时
    }

    public function testRetrieveApiArray(): void
    {
        // 使用真实的 Course 实体，避免 Mock 无法配置 getId 方法的问题
        $course = new Course();
        $course->setTitle('测试课程');

        $this->chapter->setTitle('第一章 安全基础');
        $this->chapter->setCourse($course);

        $apiArray = $this->chapter->retrieveApiArray();
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('title', $apiArray);

        $this->assertSame('第一章 安全基础', $apiArray['title']);
        $this->assertNull($apiArray['id']); // ID为null因为没有持久化
    }

    public function testFluentInterface(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：测试fluent interface模式需要一个Course对象来验证setCourse方法的链式调用
         * 必要性：验证所有setter方法都正确返回当前对象实例，支持方法链式调用
         * 替代方案：可以使用真实Course对象，但Mock对象更轻量且符合测试隔离原则
         */
        $course = $this->createMock(Course::class);

        $this->chapter->setTitle('第一章 安全基础');
        $this->chapter->setCourse($course);
        $this->chapter->setCreatedBy('user123');
        $this->chapter->setUpdatedBy('user456');
        $this->assertSame('第一章 安全基础', $this->chapter->getTitle());
        $this->assertSame($course, $this->chapter->getCourse());
        $this->assertSame('user123', $this->chapter->getCreatedBy());
        $this->assertSame('user456', $this->chapter->getUpdatedBy());
    }
}
