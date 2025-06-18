<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * Chapter 实体测试
 * 
 * 测试章节实体的基础属性、关联关系和业务方法
 */
class ChapterTest extends TestCase
{
    private Chapter $chapter;

    protected function setUp(): void
    {
        $this->chapter = new Chapter();
    }

    public function test_construct_initializes_lessons_collection(): void
    {
        $chapter = new Chapter();
        
        $this->assertCount(0, $chapter->getLessons());
    }

    public function test_toString_returns_empty_string_when_no_id(): void
    {
        $chapter = new Chapter();
        
        $this->assertSame('', (string) $chapter);
    }

    public function test_toString_returns_title_when_has_id(): void
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

    public function test_created_by_property(): void
    {
        $this->assertNull($this->chapter->getCreatedBy());
        
        $this->chapter->setCreatedBy('user123');
        $this->assertSame('user123', $this->chapter->getCreatedBy());
        
        $this->chapter->setCreatedBy(null);
        $this->assertNull($this->chapter->getCreatedBy());
    }

    public function test_updated_by_property(): void
    {
        $this->assertNull($this->chapter->getUpdatedBy());
        
        $this->chapter->setUpdatedBy('user456');
        $this->assertSame('user456', $this->chapter->getUpdatedBy());
        
        $this->chapter->setUpdatedBy(null);
        $this->assertNull($this->chapter->getUpdatedBy());
    }

    public function test_course_property(): void
    {
        $course = $this->createMock(Course::class);
        
        $this->chapter->setCourse($course);
        $this->assertSame($course, $this->chapter->getCourse());
    }

    public function test_title_property(): void
    {
        $this->chapter->setTitle('第一章 安全基础知识');
        $this->assertSame('第一章 安全基础知识', $this->chapter->getTitle());
    }

    public function test_lesson_management(): void
    {
        $lesson1 = $this->createMock(Lesson::class);
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

    public function test_lesson_count_with_empty_lessons(): void
    {
        $this->assertSame(0, $this->chapter->getLessonCount());
    }

    public function test_lesson_count_with_valid_lessons(): void
    {
        $lesson1 = $this->createMock(Lesson::class);
        $lesson1->method('getVideoUrl')->willReturn('ali://video1');
        
        $lesson2 = $this->createMock(Lesson::class);
        $lesson2->method('getVideoUrl')->willReturn('ali://video2');
        
        $lesson3 = $this->createMock(Lesson::class);
        $lesson3->method('getVideoUrl')->willReturn(null); // 无效课时
        
        $this->chapter->addLesson($lesson1);
        $this->chapter->addLesson($lesson2);
        $this->chapter->addLesson($lesson3);
        
        $this->assertSame(2, $this->chapter->getLessonCount()); // 只统计有视频URL的课时
    }

    public function test_lesson_time_with_empty_lessons(): void
    {
        $this->assertSame(0.0, $this->chapter->getLessonTime());
    }

    public function test_lesson_time_with_valid_lessons(): void
    {
        $lesson1 = $this->createMock(Lesson::class);
        $lesson1->method('getVideoUrl')->willReturn('ali://video1');
        $lesson1->method('getLessonTime')->willReturn(2.5);
        
        $lesson2 = $this->createMock(Lesson::class);
        $lesson2->method('getVideoUrl')->willReturn('ali://video2');
        $lesson2->method('getLessonTime')->willReturn(3.0);
        
        $lesson3 = $this->createMock(Lesson::class);
        $lesson3->method('getVideoUrl')->willReturn(null); // 无效课时
        $lesson3->method('getLessonTime')->willReturn(1.5);
        
        $this->chapter->addLesson($lesson1);
        $this->chapter->addLesson($lesson2);
        $this->chapter->addLesson($lesson3);
        
        $this->assertSame(5.5, $this->chapter->getLessonTime()); // 只统计有视频URL的课时
    }

    public function test_duration_second_with_empty_lessons(): void
    {
        $this->assertSame(0, $this->chapter->getDurationSecond());
    }

    public function test_duration_second_with_valid_lessons(): void
    {
        $lesson1 = $this->createMock(Lesson::class);
        $lesson1->method('getVideoUrl')->willReturn('ali://video1');
        $lesson1->method('getDurationSecond')->willReturn(1800); // 30分钟
        
        $lesson2 = $this->createMock(Lesson::class);
        $lesson2->method('getVideoUrl')->willReturn('ali://video2');
        $lesson2->method('getDurationSecond')->willReturn(2400); // 40分钟
        
        $lesson3 = $this->createMock(Lesson::class);
        $lesson3->method('getVideoUrl')->willReturn(null); // 无效课时
        $lesson3->method('getDurationSecond')->willReturn(600);
        
        $this->chapter->addLesson($lesson1);
        $this->chapter->addLesson($lesson2);
        $this->chapter->addLesson($lesson3);
        
        $this->assertSame(4200, $this->chapter->getDurationSecond()); // 只统计有视频URL的课时
    }

    public function test_retrieve_api_array(): void
    {
        $course = $this->createMock(Course::class);
        $course->method('getId')->willReturn('course123');
        
        $this->chapter->setTitle('第一章 安全基础');
        $this->chapter->setCourse($course);
        
        $apiArray = $this->chapter->retrieveApiArray();
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('title', $apiArray);
        
        $this->assertSame('第一章 安全基础', $apiArray['title']);
        $this->assertNull($apiArray['id']); // ID为null因为没有设置
    }

    public function test_fluent_interface(): void
    {
        $course = $this->createMock(Course::class);
        
        $result = $this->chapter
            ->setTitle('第一章 安全基础')
            ->setCourse($course)
            ->setCreatedBy('user123')
            ->setUpdatedBy('user456');
        
        $this->assertSame($this->chapter, $result);
        $this->assertSame('第一章 安全基础', $this->chapter->getTitle());
        $this->assertSame($course, $this->chapter->getCourse());
        $this->assertSame('user123', $this->chapter->getCreatedBy());
        $this->assertSame('user456', $this->chapter->getUpdatedBy());
    }
} 