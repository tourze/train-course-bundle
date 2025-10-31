<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;

/**
 * CourseOutline 实体测试
 *
 * 测试课程大纲实体的基础属性、关联关系和业务方法
 *
 * @internal
 */
#[CoversClass(CourseOutline::class)]
final class CourseOutlineTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CourseOutline();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'sortNumber' => ['sortNumber', 123],
            'status' => ['status', 'test_value'],
        ];
    }

    private CourseOutline $outline;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->outline = new CourseOutline();
    }

    public function testConstructInitializesProperly(): void
    {
        $outline = new CourseOutline();

        $this->assertNull($outline->getId());
        $this->assertSame(0, $outline->getSortNumber()); // 默认值
        $this->assertSame('draft', $outline->getStatus()); // 默认值
    }

    public function testCreatedByProperty(): void
    {
        $this->assertNull($this->outline->getCreatedBy());

        $this->outline->setCreatedBy('user123');
        $this->assertSame('user123', $this->outline->getCreatedBy());

        $this->outline->setCreatedBy(null);
        $this->assertNull($this->outline->getCreatedBy());
    }

    public function testUpdatedByProperty(): void
    {
        $this->assertNull($this->outline->getUpdatedBy());

        $this->outline->setUpdatedBy('user456');
        $this->assertSame('user456', $this->outline->getUpdatedBy());

        $this->outline->setUpdatedBy(null);
        $this->assertNull($this->outline->getUpdatedBy());
    }

    public function testCourseProperty(): void
    {
        $this->assertNull($this->outline->getCourse());

        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：CourseOutline实体与Course实体存在多对一关联关系，需要测试课程大纲关联
         * 必要性：验证CourseOutline实体能正确存储和返回关联的Course对象引用，支持null值设置
         * 替代方案：可以使用真实的Course实体，但Mock对象更适合单元测试的隔离性原则
         */
        $course = $this->createMock(Course::class);
        $this->outline->setCourse($course);
        $this->assertSame($course, $this->outline->getCourse());

        $this->outline->setCourse(null);
        $this->assertNull($this->outline->getCourse());
    }

    public function testTitleProperty(): void
    {
        // title is a required non-nullable string property
        // Cannot be accessed before initialization
        $this->outline->setTitle('第一章 安全生产法律法规');
        $this->assertSame('第一章 安全生产法律法规', $this->outline->getTitle());
    }

    public function testLearningObjectivesProperty(): void
    {
        $this->assertNull($this->outline->getLearningObjectives());

        $objectives = '掌握安全生产法律法规的基本知识';
        $this->outline->setLearningObjectives($objectives);
        $this->assertSame($objectives, $this->outline->getLearningObjectives());

        $this->outline->setLearningObjectives(null);
        $this->assertNull($this->outline->getLearningObjectives());
    }

    public function testContentPointsProperty(): void
    {
        $this->assertNull($this->outline->getContentPoints());

        $points = '1. 安全生产法\n2. 职业病防治法\n3. 消防法';
        $this->outline->setContentPoints($points);
        $this->assertSame($points, $this->outline->getContentPoints());

        $this->outline->setContentPoints(null);
        $this->assertNull($this->outline->getContentPoints());
    }

    public function testKeyDifficultiesProperty(): void
    {
        $this->assertNull($this->outline->getKeyDifficulties());

        $difficulties = '重点：法律条文理解\n难点：实际应用';
        $this->outline->setKeyDifficulties($difficulties);
        $this->assertSame($difficulties, $this->outline->getKeyDifficulties());

        $this->outline->setKeyDifficulties(null);
        $this->assertNull($this->outline->getKeyDifficulties());
    }

    public function testAssessmentCriteriaProperty(): void
    {
        $this->assertNull($this->outline->getAssessmentCriteria());

        $criteria = '考试成绩80分以上为合格';
        $this->outline->setAssessmentCriteria($criteria);
        $this->assertSame($criteria, $this->outline->getAssessmentCriteria());

        $this->outline->setAssessmentCriteria(null);
        $this->assertNull($this->outline->getAssessmentCriteria());
    }

    public function testReferencesProperty(): void
    {
        $this->assertNull($this->outline->getReferences());

        $references = '《安全生产法》、《职业病防治法》';
        $this->outline->setReferences($references);
        $this->assertSame($references, $this->outline->getReferences());

        $this->outline->setReferences(null);
        $this->assertNull($this->outline->getReferences());
    }

    public function testEstimatedMinutesProperty(): void
    {
        $this->assertNull($this->outline->getEstimatedMinutes());

        $this->outline->setEstimatedMinutes(120); // 2小时
        $this->assertSame(120, $this->outline->getEstimatedMinutes());

        $this->outline->setEstimatedMinutes(null);
        $this->assertNull($this->outline->getEstimatedMinutes());
    }

    public function testSortNumberProperty(): void
    {
        $this->assertSame(0, $this->outline->getSortNumber()); // 默认值

        $this->outline->setSortNumber(10);
        $this->assertSame(10, $this->outline->getSortNumber());

        $this->outline->setSortNumber(-5);
        $this->assertSame(-5, $this->outline->getSortNumber());
    }

    public function testStatusProperty(): void
    {
        $this->assertSame('draft', $this->outline->getStatus()); // 默认值

        $this->outline->setStatus('published');
        $this->assertSame('published', $this->outline->getStatus());

        $this->outline->setStatus('archived');
        $this->assertSame('archived', $this->outline->getStatus());
    }

    public function testMetadataProperty(): void
    {
        $this->assertNull($this->outline->getMetadata());

        $metadata = ['tags' => ['安全', '法律'], 'version' => '1.0'];
        $this->outline->setMetadata($metadata);
        $this->assertSame($metadata, $this->outline->getMetadata());

        $this->outline->setMetadata(null);
        $this->assertNull($this->outline->getMetadata());
    }

    public function testIsPublished(): void
    {
        // 默认状态为draft，不是published
        $this->assertFalse($this->outline->isPublished());

        // 设置为published状态
        $this->outline->setStatus('published');
        $this->assertTrue($this->outline->isPublished());

        // 设置为其他状态
        $this->outline->setStatus('archived');
        $this->assertFalse($this->outline->isPublished());

        $this->outline->setStatus('draft');
        $this->assertFalse($this->outline->isPublished());
    }

    public function testEstimatedHoursCalculation(): void
    {
        // 测试空值情况
        $this->assertSame(0.0, $this->outline->getEstimatedHours());

        // 测试60分钟 = 1小时
        $this->outline->setEstimatedMinutes(60);
        $this->assertSame(1.0, $this->outline->getEstimatedHours());

        // 测试120分钟 = 2小时
        $this->outline->setEstimatedMinutes(120);
        $this->assertSame(2.0, $this->outline->getEstimatedHours());

        // 测试90分钟 = 1.5小时
        $this->outline->setEstimatedMinutes(90);
        $this->assertSame(1.5, $this->outline->getEstimatedHours());

        // 测试精确到小数点后2位
        $this->outline->setEstimatedMinutes(75); // 1.25小时
        $this->assertSame(1.25, $this->outline->getEstimatedHours());

        // 测试需要四舍五入的情况
        $this->outline->setEstimatedMinutes(77); // 1.283333... 小时
        $this->assertSame(1.28, $this->outline->getEstimatedHours());
    }

    public function testFluentInterface(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：测试setter方法需要一个Course对象来验证setCourse方法
         * 必要性：验证CourseOutline实体所有setter方法都能正确设置属性值
         * 替代方案：可以使用真实Course对象，但Mock对象更轻量且符合测试隔离原则
         */
        $course = $this->createMock(Course::class);

        $this->outline->setTitle('第一章 安全生产法律法规');
        $this->outline->setCourse($course);
        $this->outline->setLearningObjectives('掌握安全生产法律法规');
        $this->outline->setContentPoints('法律条文学习');
        $this->outline->setKeyDifficulties('实际应用');
        $this->outline->setAssessmentCriteria('考试80分以上');
        $this->outline->setReferences('安全生产法');
        $this->outline->setEstimatedMinutes(120);
        $this->outline->setSortNumber(1);
        $this->outline->setStatus('published');
        $this->outline->setMetadata(['version' => '1.0']);
        $this->outline->setCreatedBy('user123');
        $this->outline->setUpdatedBy('user456');

        $this->assertSame('第一章 安全生产法律法规', $this->outline->getTitle());
        $this->assertSame($course, $this->outline->getCourse());
        $this->assertSame('掌握安全生产法律法规', $this->outline->getLearningObjectives());
        $this->assertSame('法律条文学习', $this->outline->getContentPoints());
        $this->assertSame('实际应用', $this->outline->getKeyDifficulties());
        $this->assertSame('考试80分以上', $this->outline->getAssessmentCriteria());
        $this->assertSame('安全生产法', $this->outline->getReferences());
        $this->assertSame(120, $this->outline->getEstimatedMinutes());
        $this->assertSame(1, $this->outline->getSortNumber());
        $this->assertSame('published', $this->outline->getStatus());
        $this->assertSame(['version' => '1.0'], $this->outline->getMetadata());
        $this->assertSame('user123', $this->outline->getCreatedBy());
        $this->assertSame('user456', $this->outline->getUpdatedBy());
    }

    public function testStatusValues(): void
    {
        // 测试常见的状态值
        $statuses = ['draft', 'published', 'archived', 'reviewing'];

        foreach ($statuses as $status) {
            $this->outline->setStatus($status);
            $this->assertSame($status, $this->outline->getStatus());

            // 只有published状态返回true
            $this->assertSame('published' === $status, $this->outline->isPublished());
        }
    }

    public function testEstimatedMinutesEdgeCases(): void
    {
        // 测试边界值
        $this->outline->setEstimatedMinutes(0);
        $this->assertSame(0, $this->outline->getEstimatedMinutes());
        $this->assertSame(0.0, $this->outline->getEstimatedHours());

        $this->outline->setEstimatedMinutes(1);
        $this->assertSame(1, $this->outline->getEstimatedMinutes());
        $this->assertSame(0.02, $this->outline->getEstimatedHours()); // 四舍五入到0.02

        // 测试大数值
        $this->outline->setEstimatedMinutes(1440); // 24小时
        $this->assertSame(1440, $this->outline->getEstimatedMinutes());
        $this->assertSame(24.0, $this->outline->getEstimatedHours());
    }

    public function testMetadataComplexStructure(): void
    {
        $complexMetadata = [
            'tags' => ['安全', '法律', '培训'],
            'version' => '2.1',
            'author' => '张老师',
            'difficulty' => 'intermediate',
            'prerequisites' => ['基础安全知识'],
            'resources' => [
                'videos' => ['video1.mp4', 'video2.mp4'],
                'documents' => ['doc1.pdf', 'doc2.pdf'],
            ],
        ];

        $this->outline->setMetadata($complexMetadata);
        $this->assertSame($complexMetadata, $this->outline->getMetadata());

        // 测试可以访问嵌套数据
        $metadata = $this->outline->getMetadata();

        // 验证数据结构完整性
        $this->assertArrayHasKey('tags', $metadata);
        $this->assertArrayHasKey('author', $metadata);
        $this->assertArrayHasKey('resources', $metadata);

        // 验证数据类型正确性
        $this->assertSame(['安全', '法律', '培训'], $metadata['tags']);
        $this->assertSame('张老师', $metadata['author']);
        $this->assertSame(['videos' => ['video1.mp4', 'video2.mp4'], 'documents' => ['doc1.pdf', 'doc2.pdf']], $metadata['resources']);

        // 验证标签数量和内容
        $this->assertCount(3, $metadata['tags']);
        $this->assertContains('安全', $metadata['tags']);
        $this->assertContains('培训', $metadata['tags']);
        $this->assertContains('法律', $metadata['tags']);

        // 验证作者信息格式
        $this->assertMatchesRegularExpression('/\S+老师/', $metadata['author']);

        // 验证资源结构和数量
        $this->assertArrayHasKey('videos', $metadata['resources']);
        $this->assertSame(['video1.mp4', 'video2.mp4'], $metadata['resources']['videos']);
        $this->assertCount(2, $metadata['resources']['videos']);
        $this->assertContains('video1.mp4', $metadata['resources']['videos']);
    }
}
