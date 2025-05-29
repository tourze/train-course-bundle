<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * Course 实体单元测试
 */
class CourseTest extends TestCase
{
    private Course $course;

    protected function setUp(): void
    {
        $this->course = new Course();
    }

    public function test_construct_initializes_collections(): void
    {
        $course = new Course();
        
        $this->assertCount(0, $course->getChapters());
        $this->assertCount(0, $course->getOutlines());
        $this->assertCount(0, $course->getCollects());
        $this->assertCount(0, $course->getEvaluates());
    }

    public function test_toString_returns_empty_string_when_no_id(): void
    {
        $course = new Course();
        
        $this->assertSame('', (string) $course);
    }

    public function test_toString_returns_title_when_has_id(): void
    {
        $course = new Course();
        $course->setTitle('测试课程');
        
        // 模拟有ID的情况
        $reflection = new \ReflectionClass($course);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($course, '123456789');
        
        $this->assertSame('测试课程', (string) $course);
    }

    public function test_created_by_property(): void
    {
        $this->assertNull($this->course->getCreatedBy());
        
        $this->course->setCreatedBy('user123');
        $this->assertSame('user123', $this->course->getCreatedBy());
        
        $this->course->setCreatedBy(null);
        $this->assertNull($this->course->getCreatedBy());
    }

    public function test_updated_by_property(): void
    {
        $this->assertNull($this->course->getUpdatedBy());
        
        $this->course->setUpdatedBy('user456');
        $this->assertSame('user456', $this->course->getUpdatedBy());
        
        $this->course->setUpdatedBy(null);
        $this->assertNull($this->course->getUpdatedBy());
    }

    public function test_valid_property(): void
    {
        $this->assertFalse($this->course->isValid());
        
        $this->course->setValid(true);
        $this->assertTrue($this->course->isValid());
        
        $this->course->setValid(false);
        $this->assertFalse($this->course->isValid());
        
        $this->course->setValid(null);
        $this->assertNull($this->course->isValid());
    }

    public function test_category_property(): void
    {
        $category = $this->createMock(Category::class);
        
        $this->course->setCategory($category);
        $this->assertSame($category, $this->course->getCategory());
    }

    public function test_title_property(): void
    {
        $this->course->setTitle('安全生产培训课程');
        $this->assertSame('安全生产培训课程', $this->course->getTitle());
    }

    public function test_instructor_property(): void
    {
        $this->assertNull($this->course->getInstructor());
        
        $instructor = $this->createMock(UserInterface::class);
        $this->course->setInstructor($instructor);
        $this->assertSame($instructor, $this->course->getInstructor());
        
        $this->course->setInstructor(null);
        $this->assertNull($this->course->getInstructor());
    }

    public function test_valid_day_property(): void
    {
        $this->assertSame(365, $this->course->getValidDay());
        
        $this->course->setValidDay(180);
        $this->assertSame(180, $this->course->getValidDay());
    }

    public function test_learn_hour_property(): void
    {
        $this->assertNull($this->course->getLearnHour());
        
        $this->course->setLearnHour(8);
        $this->assertSame(8, $this->course->getLearnHour());
    }

    public function test_teacher_name_property(): void
    {
        $this->assertNull($this->course->getTeacherName());
        
        $this->course->setTeacherName('张老师');
        $this->assertSame('张老师', $this->course->getTeacherName());
        
        $this->course->setTeacherName(null);
        $this->assertNull($this->course->getTeacherName());
    }

    public function test_cover_thumb_property(): void
    {
        $this->assertNull($this->course->getCoverThumb());
        
        $this->course->setCoverThumb('/images/course-cover.jpg');
        $this->assertSame('/images/course-cover.jpg', $this->course->getCoverThumb());
        
        $this->course->setCoverThumb(null);
        $this->assertNull($this->course->getCoverThumb());
    }

    public function test_description_property(): void
    {
        $this->assertNull($this->course->getDescription());
        
        $description = '这是一门关于安全生产的培训课程';
        $this->course->setDescription($description);
        $this->assertSame($description, $this->course->getDescription());
        
        $this->course->setDescription(null);
        $this->assertNull($this->course->getDescription());
    }

    public function test_price_property(): void
    {
        $this->assertSame('20.00', $this->course->getPrice());
        
        $this->course->setPrice('99.99');
        $this->assertSame('99.99', $this->course->getPrice());
        
        $this->course->setPrice(null);
        $this->assertNull($this->course->getPrice());
    }

    public function test_chapter_management(): void
    {
        $chapter1 = $this->createMock(Chapter::class);
        $chapter2 = $this->createMock(Chapter::class);
        
        // 测试添加章节
        $this->course->addChapter($chapter1);
        $this->assertCount(1, $this->course->getChapters());
        $this->assertTrue($this->course->getChapters()->contains($chapter1));
        
        $this->course->addChapter($chapter2);
        $this->assertCount(2, $this->course->getChapters());
        
        // 测试重复添加同一章节
        $this->course->addChapter($chapter1);
        $this->assertCount(2, $this->course->getChapters());
        
        // 测试移除章节
        $this->course->removeChapter($chapter1);
        $this->assertCount(1, $this->course->getChapters());
        $this->assertFalse($this->course->getChapters()->contains($chapter1));
        
        // 测试移除不存在的章节
        $this->course->removeChapter($chapter1);
        $this->assertCount(1, $this->course->getChapters());
    }

    public function test_chapter_count(): void
    {
        $this->assertSame(0, $this->course->getChapterCount());
        
        $chapter1 = $this->createMock(Chapter::class);
        $chapter2 = $this->createMock(Chapter::class);
        
        $this->course->addChapter($chapter1);
        $this->assertSame(1, $this->course->getChapterCount());
        
        $this->course->addChapter($chapter2);
        $this->assertSame(2, $this->course->getChapterCount());
    }

    public function test_outline_management(): void
    {
        $outline1 = $this->createMock(CourseOutline::class);
        $outline2 = $this->createMock(CourseOutline::class);
        
        // 测试添加大纲
        $this->course->addOutline($outline1);
        $this->assertCount(1, $this->course->getOutlines());
        $this->assertTrue($this->course->getOutlines()->contains($outline1));
        
        $this->course->addOutline($outline2);
        $this->assertCount(2, $this->course->getOutlines());
        
        // 测试重复添加同一大纲
        $this->course->addOutline($outline1);
        $this->assertCount(2, $this->course->getOutlines());
        
        // 测试移除大纲
        $this->course->removeOutline($outline1);
        $this->assertCount(1, $this->course->getOutlines());
        $this->assertFalse($this->course->getOutlines()->contains($outline1));
    }

    public function test_outline_count(): void
    {
        $this->assertSame(0, $this->course->getOutlineCount());
        
        $outline1 = $this->createMock(CourseOutline::class);
        $outline2 = $this->createMock(CourseOutline::class);
        
        $this->course->addOutline($outline1);
        $this->assertSame(1, $this->course->getOutlineCount());
        
        $this->course->addOutline($outline2);
        $this->assertSame(2, $this->course->getOutlineCount());
    }

    public function test_collect_management(): void
    {
        $collect1 = $this->createMock(Collect::class);
        $collect2 = $this->createMock(Collect::class);
        
        // 测试添加收藏
        $this->course->addCollect($collect1);
        $this->assertCount(1, $this->course->getCollects());
        $this->assertTrue($this->course->getCollects()->contains($collect1));
        
        $this->course->addCollect($collect2);
        $this->assertCount(2, $this->course->getCollects());
        
        // 测试重复添加同一收藏
        $this->course->addCollect($collect1);
        $this->assertCount(2, $this->course->getCollects());
        
        // 测试移除收藏
        $this->course->removeCollect($collect1);
        $this->assertCount(1, $this->course->getCollects());
        $this->assertFalse($this->course->getCollects()->contains($collect1));
    }

    public function test_collect_count(): void
    {
        $this->assertSame(0, $this->course->getCollectCount());
        
        $collect1 = $this->createMock(Collect::class);
        $collect2 = $this->createMock(Collect::class);
        
        $this->course->addCollect($collect1);
        $this->assertSame(1, $this->course->getCollectCount());
        
        $this->course->addCollect($collect2);
        $this->assertSame(2, $this->course->getCollectCount());
    }

    public function test_evaluate_management(): void
    {
        $evaluate1 = $this->createMock(Evaluate::class);
        $evaluate2 = $this->createMock(Evaluate::class);
        
        // 测试添加评价
        $this->course->addEvaluate($evaluate1);
        $this->assertCount(1, $this->course->getEvaluates());
        $this->assertTrue($this->course->getEvaluates()->contains($evaluate1));
        
        $this->course->addEvaluate($evaluate2);
        $this->assertCount(2, $this->course->getEvaluates());
        
        // 测试重复添加同一评价
        $this->course->addEvaluate($evaluate1);
        $this->assertCount(2, $this->course->getEvaluates());
        
        // 测试移除评价
        $this->course->removeEvaluate($evaluate1);
        $this->assertCount(1, $this->course->getEvaluates());
        $this->assertFalse($this->course->getEvaluates()->contains($evaluate1));
    }

    public function test_evaluate_count(): void
    {
        $this->assertSame(0, $this->course->getEvaluateCount());
        
        $evaluate1 = $this->createMock(Evaluate::class);
        $evaluate2 = $this->createMock(Evaluate::class);
        
        $this->course->addEvaluate($evaluate1);
        $this->assertSame(1, $this->course->getEvaluateCount());
        
        $this->course->addEvaluate($evaluate2);
        $this->assertSame(2, $this->course->getEvaluateCount());
    }

    public function test_retrieve_api_array(): void
    {
        $category = $this->createMock(Category::class);
        $category->method('retrieveApiArray')->willReturn(['id' => 'cat123', 'name' => '安全培训']);
        
        $this->course->setTitle('测试课程');
        $this->course->setDescription('课程描述');
        $this->course->setCoverThumb('/images/cover.jpg');
        $this->course->setPrice('99.99');
        $this->course->setValidDay(180);
        $this->course->setLearnHour(8);
        $this->course->setTeacherName('张老师');
        $this->course->setCategory($category);
        
        $apiArray = $this->course->retrieveApiArray();
        
        $this->assertIsArray($apiArray);
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('category', $apiArray);
        $this->assertArrayHasKey('title', $apiArray);
        $this->assertArrayHasKey('description', $apiArray);
        $this->assertArrayHasKey('plainDescription', $apiArray);
        $this->assertArrayHasKey('coverThumb', $apiArray);
        $this->assertArrayHasKey('validDay', $apiArray);
        $this->assertArrayHasKey('learnHour', $apiArray);
        $this->assertArrayHasKey('teacherName', $apiArray);
        $this->assertArrayHasKey('lessonCount', $apiArray);
        $this->assertArrayHasKey('lessonTime', $apiArray);
        $this->assertArrayHasKey('chapterCount', $apiArray);
        $this->assertArrayHasKey('durationSecond', $apiArray);
        $this->assertArrayHasKey('registrationCount', $apiArray);
        
        $this->assertSame('测试课程', $apiArray['title']);
        $this->assertSame('课程描述', $apiArray['description']);
        $this->assertSame('课程描述', $apiArray['plainDescription']);
        $this->assertSame('/images/cover.jpg', $apiArray['coverThumb']);
        $this->assertSame(180, $apiArray['validDay']);
        $this->assertSame(8, $apiArray['learnHour']);
        $this->assertSame('张老师', $apiArray['teacherName']);
        $this->assertSame(0, $apiArray['lessonCount']);
        $this->assertSame(0.0, $apiArray['lessonTime']);
        $this->assertSame(0, $apiArray['chapterCount']);
        $this->assertSame(0, $apiArray['durationSecond']);
        $this->assertSame(0, $apiArray['registrationCount']);
        $this->assertIsArray($apiArray['category']);
    }

    public function test_retrieve_admin_array(): void
    {
        $this->course->setTitle('测试课程');
        $this->course->setDescription('课程描述');
        $this->course->setCoverThumb('/images/cover.jpg');
        $this->course->setPrice('99.99');
        $this->course->setValidDay(180);
        $this->course->setLearnHour(8);
        $this->course->setTeacherName('张老师');
        
        $adminArray = $this->course->retrieveAdminArray();
        
        $this->assertIsArray($adminArray);
        $this->assertArrayHasKey('title', $adminArray);
        $this->assertArrayHasKey('description', $adminArray);
        $this->assertArrayHasKey('coverThumb', $adminArray);
        $this->assertArrayHasKey('price', $adminArray);
        $this->assertArrayHasKey('validDay', $adminArray);
        $this->assertArrayHasKey('learnHour', $adminArray);
        $this->assertArrayHasKey('teacherName', $adminArray);
    }

    public function test_lesson_count_with_empty_chapters(): void
    {
        $this->assertSame(0, $this->course->getLessonCount());
    }

    public function test_lesson_time_with_empty_chapters(): void
    {
        $this->assertSame(0.0, $this->course->getLessonTime());
    }

    public function test_duration_second_with_empty_chapters(): void
    {
        $this->assertSame(0, $this->course->getDurationSecond());
    }

    public function test_average_rating_with_no_evaluates(): void
    {
        $this->assertSame(0.0, $this->course->getAverageRating());
    }

    public function test_fluent_interface(): void
    {
        $category = $this->createMock(Category::class);
        $instructor = $this->createMock(UserInterface::class);
        
        $result = $this->course
            ->setTitle('测试课程')
            ->setCategory($category)
            ->setInstructor($instructor)
            ->setValidDay(180)
            ->setLearnHour(8)
            ->setTeacherName('张老师')
            ->setCoverThumb('/images/cover.jpg')
            ->setDescription('课程描述')
            ->setPrice('99.99')
            ->setValid(true);
        
        $this->assertSame($this->course, $result);
        $this->assertSame('测试课程', $this->course->getTitle());
        $this->assertSame($category, $this->course->getCategory());
        $this->assertSame($instructor, $this->course->getInstructor());
        $this->assertSame(180, $this->course->getValidDay());
        $this->assertSame(8, $this->course->getLearnHour());
        $this->assertSame('张老师', $this->course->getTeacherName());
        $this->assertSame('/images/cover.jpg', $this->course->getCoverThumb());
        $this->assertSame('课程描述', $this->course->getDescription());
        $this->assertSame('99.99', $this->course->getPrice());
        $this->assertTrue($this->course->isValid());
    }
}
