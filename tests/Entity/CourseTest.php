<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * Course 实体单元测试
 *
 * @internal
 */
#[CoversClass(Course::class)]
final class CourseTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Course();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'title' => ['title', 'test_value'],
            'validDay' => ['validDay', 123],
        ];
    }

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->course = new Course();
    }

    public function testConstructInitializesCollections(): void
    {
        $course = new Course();

        $this->assertCount(0, $course->getChapters());
        $this->assertCount(0, $course->getOutlines());
        $this->assertCount(0, $course->getCollects());
        $this->assertCount(0, $course->getEvaluates());
    }

    public function testToStringReturnsEmptyStringWhenNoId(): void
    {
        $course = new Course();

        $this->assertSame('', (string) $course);
    }

    public function testToStringReturnsTitleWhenHasId(): void
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

    public function testCreatedByProperty(): void
    {
        $this->assertNull($this->course->getCreatedBy());

        $this->course->setCreatedBy('user123');
        $this->assertSame('user123', $this->course->getCreatedBy());

        $this->course->setCreatedBy(null);
        $this->assertNull($this->course->getCreatedBy());
    }

    public function testUpdatedByProperty(): void
    {
        $this->assertNull($this->course->getUpdatedBy());

        $this->course->setUpdatedBy('user456');
        $this->assertSame('user456', $this->course->getUpdatedBy());

        $this->course->setUpdatedBy(null);
        $this->assertNull($this->course->getUpdatedBy());
    }

    public function testValidProperty(): void
    {
        $this->assertFalse($this->course->isValid());

        $this->course->setValid(true);
        $this->assertTrue($this->course->isValid());

        $this->course->setValid(false);
        $this->assertFalse($this->course->isValid());

        $this->course->setValid(null);
        $this->assertNull($this->course->isValid());
    }

    public function testCategoryProperty(): void
    {
        /*
         * 使用具体的Catalog实体类创建Mock对象
         * 原因：Course实体与Catalog实体存在多对一关联关系，需要测试课程分类关联
         * 必要性：验证Course实体能正确存储和返回关联的Catalog对象引用
         * 替代方案：可以使用真实的Catalog实体，但Mock对象更适合单元测试的隔离性原则
         */
        $category = $this->createMock(Catalog::class);

        $this->course->setCategory($category);
        $this->assertSame($category, $this->course->getCategory());
    }

    public function testTitleProperty(): void
    {
        $this->course->setTitle('安全生产培训课程');
        $this->assertSame('安全生产培训课程', $this->course->getTitle());
    }

    public function testInstructorProperty(): void
    {
        $this->assertNull($this->course->getInstructor());

        $instructor = $this->createMock(UserInterface::class);
        $this->course->setInstructor($instructor);
        $this->assertSame($instructor, $this->course->getInstructor());

        $this->course->setInstructor(null);
        $this->assertNull($this->course->getInstructor());
    }

    public function testValidDayProperty(): void
    {
        $this->assertSame(365, $this->course->getValidDay());

        $this->course->setValidDay(180);
        $this->assertSame(180, $this->course->getValidDay());
    }

    public function testLearnHourProperty(): void
    {
        $this->assertNull($this->course->getLearnHour());

        $this->course->setLearnHour(8);
        $this->assertSame(8, $this->course->getLearnHour());
    }

    public function testTeacherNameProperty(): void
    {
        $this->assertNull($this->course->getTeacherName());

        $this->course->setTeacherName('张老师');
        $this->assertSame('张老师', $this->course->getTeacherName());

        $this->course->setTeacherName(null);
        $this->assertNull($this->course->getTeacherName());
    }

    public function testCoverThumbProperty(): void
    {
        $this->assertNull($this->course->getCoverThumb());

        $this->course->setCoverThumb('/images/course-cover.jpg');
        $this->assertSame('/images/course-cover.jpg', $this->course->getCoverThumb());

        $this->course->setCoverThumb(null);
        $this->assertNull($this->course->getCoverThumb());
    }

    public function testDescriptionProperty(): void
    {
        $this->assertNull($this->course->getDescription());

        $description = '这是一门关于安全生产的培训课程';
        $this->course->setDescription($description);
        $this->assertSame($description, $this->course->getDescription());

        $this->course->setDescription(null);
        $this->assertNull($this->course->getDescription());
    }

    public function testPriceProperty(): void
    {
        $this->assertSame('20.00', $this->course->getPrice());

        $this->course->setPrice('99.99');
        $this->assertSame('99.99', $this->course->getPrice());

        $this->course->setPrice(null);
        $this->assertNull($this->course->getPrice());
    }

    public function testChapterManagement(): void
    {
        /*
         * 使用具体的Chapter实体类创建Mock对象
         * 原因：Course实体与Chapter实体存在一对多关联关系，需要测试章节集合的管理功能
         * 必要性：验证addChapter、removeChapter等集合操作方法，测试章节关联的增删功能
         * 替代方案：可以使用真实的Chapter实体对象，但Mock能避免复杂的实体初始化
         */
        $chapter1 = $this->createMock(Chapter::class);
        /* 使用具体的Chapter实体类创建第二个Mock对象，与chapter1相同的使用原因 */
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

    public function testChapterCount(): void
    {
        $this->assertSame(0, $this->course->getChapterCount());

        /*
         * 使用具体的Chapter实体类创建Mock对象
         * 原因：测试getChapterCount方法需要向章节集合添加Chapter实体来验证计数功能
         * 必要性：验证章节数量统计的准确性，确保集合操作正确反映在计数结果中
         * 替代方案：真实Chapter对象需要更多属性设置，Mock对象更专注于测试计数逻辑
         */
        $chapter1 = $this->createMock(Chapter::class);
        /* 使用具体的Chapter实体类创建第二个Mock对象，与chapter1相同的使用原因 */
        $chapter2 = $this->createMock(Chapter::class);

        $this->course->addChapter($chapter1);
        $this->assertSame(1, $this->course->getChapterCount());

        $this->course->addChapter($chapter2);
        $this->assertSame(2, $this->course->getChapterCount());
    }

    public function testOutlineManagement(): void
    {
        /*
         * 使用具体的CourseOutline实体类创建Mock对象
         * 原因：Course实体与CourseOutline实体存在一对多关联关系，需要测试课程大纲集合的管理功能
         * 必要性：验证addOutline、removeOutline等集合操作方法，测试大纲关联的增删功能
         * 替代方案：可以使用真实的CourseOutline实体对象，但Mock能简化测试并专注于集合操作逻辑
         */
        $outline1 = $this->createMock(CourseOutline::class);
        /* 使用具体的CourseOutline实体类创建第二个Mock对象，与outline1相同的使用原因 */
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

    public function testOutlineCount(): void
    {
        $this->assertSame(0, $this->course->getOutlineCount());

        /*
         * 使用具体的CourseOutline实体类创建Mock对象
         * 原因：测试getOutlineCount方法需要向大纲集合添加CourseOutline实体来验证计数功能
         * 必要性：验证课程大纲数量统计的准确性，确保集合操作正确反映在计数结果中
         * 替代方案：真实CourseOutline对象需要更多属性设置，Mock对象更专注于测试计数逻辑
         */
        $outline1 = $this->createMock(CourseOutline::class);
        /* 使用具体的CourseOutline实体类创建第二个Mock对象，与outline1相同的使用原因 */
        $outline2 = $this->createMock(CourseOutline::class);

        $this->course->addOutline($outline1);
        $this->assertSame(1, $this->course->getOutlineCount());

        $this->course->addOutline($outline2);
        $this->assertSame(2, $this->course->getOutlineCount());
    }

    public function testCollectManagement(): void
    {
        /*
         * 使用具体的Collect实体类创建Mock对象
         * 原因：Course实体与Collect实体存在一对多关联关系，需要测试课程收藏集合的管理功能
         * 必要性：验证addCollect、removeCollect等集合操作方法，测试收藏关联的增删功能
         * 替代方案：可以使用真实的Collect实体对象，但Mock能避免用户依赖和复杂的实体初始化
         */
        $collect1 = $this->createMock(Collect::class);
        /* 使用具体的Collect实体类创建第二个Mock对象，与collect1相同的使用原因 */
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

    public function testCollectCount(): void
    {
        $this->assertSame(0, $this->course->getCollectCount());

        /*
         * 使用具体的Collect实体类创建Mock对象
         * 原因：测试getCollectCount方法需要向收藏集合添加Collect实体来验证计数功能
         * 必要性：验证课程收藏数量统计的准确性，确保集合操作正确反映在计数结果中
         * 替代方案：真实Collect对象涉及用户关系，Mock对象更适合单纯测试计数逻辑
         */
        $collect1 = $this->createMock(Collect::class);
        /* 使用具体的Collect实体类创建第二个Mock对象，与collect1相同的使用原因 */
        $collect2 = $this->createMock(Collect::class);

        $this->course->addCollect($collect1);
        $this->assertSame(1, $this->course->getCollectCount());

        $this->course->addCollect($collect2);
        $this->assertSame(2, $this->course->getCollectCount());
    }

    public function testEvaluateManagement(): void
    {
        /*
         * 使用具体的Evaluate实体类创建Mock对象
         * 原因：Course实体与Evaluate实体存在一对多关联关系，需要测试课程评价集合的管理功能
         * 必要性：验证addEvaluate、removeEvaluate等集合操作方法，测试评价关联的增删功能
         * 替代方案：可以使用真实的Evaluate实体对象，但Mock能避免用户和评分依赖的复杂初始化
         */
        $evaluate1 = $this->createMock(Evaluate::class);
        /* 使用具体的Evaluate实体类创建第二个Mock对象，与evaluate1相同的使用原因 */
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

    public function testEvaluateCount(): void
    {
        $this->assertSame(0, $this->course->getEvaluateCount());

        /*
         * 使用具体的Evaluate实体类创建Mock对象
         * 原因：测试getEvaluateCount方法需要向评价集合添加Evaluate实体来验证计数功能
         * 必要性：验证课程评价数量统计的准确性，确保集合操作正确反映在计数结果中
         * 替代方案：真实Evaluate对象涉及用户和评分数据，Mock对象更专注于测试计数逻辑
         */
        $evaluate1 = $this->createMock(Evaluate::class);
        /* 使用具体的Evaluate实体类创建第二个Mock对象，与evaluate1相同的使用原因 */
        $evaluate2 = $this->createMock(Evaluate::class);

        $this->course->addEvaluate($evaluate1);
        $this->assertSame(1, $this->course->getEvaluateCount());

        $this->course->addEvaluate($evaluate2);
        $this->assertSame(2, $this->course->getEvaluateCount());
    }

    public function testRetrieveApiArray(): void
    {
        /*
         * 使用具体的Catalog实体类创建Mock对象
         * 原因：测试retrieveApiArray方法需要调用关联Catalog实体的getId和getName方法获取分类信息
         * 必要性：验证API数组输出中包含正确的分类信息，需要Mock Catalog的方法返回预期数据
         * 替代方案：使用真实Catalog对象需要完整的分类数据初始化，Mock更适合控制返回值
         */
        $category = $this->createMock(Catalog::class);
        $category->method('getId')->willReturn('cat123');
        $category->method('getName')->willReturn('安全培训');

        $this->course->setTitle('测试课程');
        $this->course->setDescription('课程描述');
        $this->course->setCoverThumb('/images/cover.jpg');
        $this->course->setPrice('99.99');
        $this->course->setValidDay(180);
        $this->course->setLearnHour(8);
        $this->course->setTeacherName('张老师');
        $this->course->setCategory($category);

        $apiArray = $this->course->retrieveApiArray();
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
    }

    public function testRetrieveAdminArray(): void
    {
        $this->course->setTitle('测试课程');
        $this->course->setDescription('课程描述');
        $this->course->setCoverThumb('/images/cover.jpg');
        $this->course->setPrice('99.99');
        $this->course->setValidDay(180);
        $this->course->setLearnHour(8);
        $this->course->setTeacherName('张老师');

        $adminArray = $this->course->retrieveAdminArray();
        $this->assertArrayHasKey('title', $adminArray);
        $this->assertArrayHasKey('description', $adminArray);
        $this->assertArrayHasKey('coverThumb', $adminArray);
        $this->assertArrayHasKey('price', $adminArray);
        $this->assertArrayHasKey('validDay', $adminArray);
        $this->assertArrayHasKey('learnHour', $adminArray);
        $this->assertArrayHasKey('teacherName', $adminArray);
    }

    public function testLessonCountWithEmptyChapters(): void
    {
        $this->assertSame(0, $this->course->getLessonCount());
    }

    public function testLessonTimeWithEmptyChapters(): void
    {
        $this->assertSame(0.0, $this->course->getLessonTime());
    }

    public function testDurationSecondWithEmptyChapters(): void
    {
        $this->assertSame(0, $this->course->getDurationSecond());
    }

    public function testAverageRatingWithNoEvaluates(): void
    {
        $this->assertSame(0.0, $this->course->getAverageRating());
    }

    public function testFluentInterface(): void
    {
        /*
         * 使用具体的Catalog实体类创建Mock对象
         * 原因：测试setter方法需要一个Catalog对象来验证setCategory方法
         * 必要性：验证所有setter方法都能正确设置属性值
         * 替代方案：可以使用真实Catalog对象，但Mock对象更轻量且符合测试隔离原则
         */
        $category = $this->createMock(Catalog::class);
        $instructor = $this->createMock(UserInterface::class);

        $this->course->setTitle('测试课程');
        $this->course->setCategory($category);
        $this->course->setInstructor($instructor);
        $this->course->setValidDay(180);
        $this->course->setLearnHour(8);
        $this->course->setTeacherName('张老师');
        $this->course->setCoverThumb('/images/cover.jpg');
        $this->course->setDescription('课程描述');
        $this->course->setPrice('99.99');
        $this->course->setValid(true);

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
