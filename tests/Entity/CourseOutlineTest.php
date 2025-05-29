<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;

/**
 * CourseOutline 实体测试
 * 
 * 测试课程大纲实体的基础属性、关联关系和业务方法
 */
class CourseOutlineTest extends TestCase
{
    private CourseOutline $outline;

    protected function setUp(): void
    {
        $this->outline = new CourseOutline();
    }

    public function test_construct_initializes_properly(): void
    {
        $outline = new CourseOutline();
        
        $this->assertNull($outline->getId());
        $this->assertSame(0, $outline->getSortNumber()); // 默认值
        $this->assertSame('draft', $outline->getStatus()); // 默认值
    }

    public function test_created_by_property(): void
    {
        $this->assertNull($this->outline->getCreatedBy());
        
        $this->outline->setCreatedBy('user123');
        $this->assertSame('user123', $this->outline->getCreatedBy());
        
        $this->outline->setCreatedBy(null);
        $this->assertNull($this->outline->getCreatedBy());
    }

    public function test_updated_by_property(): void
    {
        $this->assertNull($this->outline->getUpdatedBy());
        
        $this->outline->setUpdatedBy('user456');
        $this->assertSame('user456', $this->outline->getUpdatedBy());
        
        $this->outline->setUpdatedBy(null);
        $this->assertNull($this->outline->getUpdatedBy());
    }

    public function test_course_property(): void
    {
        $this->assertNull($this->outline->getCourse());
        
        $course = $this->createMock(Course::class);
        $this->outline->setCourse($course);
        $this->assertSame($course, $this->outline->getCourse());
        
        $this->outline->setCourse(null);
        $this->assertNull($this->outline->getCourse());
    }

    public function test_title_property(): void
    {
        $this->assertNull($this->outline->getTitle());
        
        $this->outline->setTitle('第一章 安全生产法律法规');
        $this->assertSame('第一章 安全生产法律法规', $this->outline->getTitle());
    }

    public function test_learning_objectives_property(): void
    {
        $this->assertNull($this->outline->getLearningObjectives());
        
        $objectives = '掌握安全生产法律法规的基本知识';
        $this->outline->setLearningObjectives($objectives);
        $this->assertSame($objectives, $this->outline->getLearningObjectives());
        
        $this->outline->setLearningObjectives(null);
        $this->assertNull($this->outline->getLearningObjectives());
    }

    public function test_content_points_property(): void
    {
        $this->assertNull($this->outline->getContentPoints());
        
        $points = '1. 安全生产法\n2. 职业病防治法\n3. 消防法';
        $this->outline->setContentPoints($points);
        $this->assertSame($points, $this->outline->getContentPoints());
        
        $this->outline->setContentPoints(null);
        $this->assertNull($this->outline->getContentPoints());
    }

    public function test_key_difficulties_property(): void
    {
        $this->assertNull($this->outline->getKeyDifficulties());
        
        $difficulties = '重点：法律条文理解\n难点：实际应用';
        $this->outline->setKeyDifficulties($difficulties);
        $this->assertSame($difficulties, $this->outline->getKeyDifficulties());
        
        $this->outline->setKeyDifficulties(null);
        $this->assertNull($this->outline->getKeyDifficulties());
    }

    public function test_assessment_criteria_property(): void
    {
        $this->assertNull($this->outline->getAssessmentCriteria());
        
        $criteria = '考试成绩80分以上为合格';
        $this->outline->setAssessmentCriteria($criteria);
        $this->assertSame($criteria, $this->outline->getAssessmentCriteria());
        
        $this->outline->setAssessmentCriteria(null);
        $this->assertNull($this->outline->getAssessmentCriteria());
    }

    public function test_references_property(): void
    {
        $this->assertNull($this->outline->getReferences());
        
        $references = '《安全生产法》、《职业病防治法》';
        $this->outline->setReferences($references);
        $this->assertSame($references, $this->outline->getReferences());
        
        $this->outline->setReferences(null);
        $this->assertNull($this->outline->getReferences());
    }

    public function test_estimated_minutes_property(): void
    {
        $this->assertNull($this->outline->getEstimatedMinutes());
        
        $this->outline->setEstimatedMinutes(120); // 2小时
        $this->assertSame(120, $this->outline->getEstimatedMinutes());
        
        $this->outline->setEstimatedMinutes(null);
        $this->assertNull($this->outline->getEstimatedMinutes());
    }

    public function test_sort_number_property(): void
    {
        $this->assertSame(0, $this->outline->getSortNumber()); // 默认值
        
        $this->outline->setSortNumber(10);
        $this->assertSame(10, $this->outline->getSortNumber());
        
        $this->outline->setSortNumber(-5);
        $this->assertSame(-5, $this->outline->getSortNumber());
    }

    public function test_status_property(): void
    {
        $this->assertSame('draft', $this->outline->getStatus()); // 默认值
        
        $this->outline->setStatus('published');
        $this->assertSame('published', $this->outline->getStatus());
        
        $this->outline->setStatus('archived');
        $this->assertSame('archived', $this->outline->getStatus());
    }

    public function test_metadata_property(): void
    {
        $this->assertNull($this->outline->getMetadata());
        
        $metadata = ['tags' => ['安全', '法律'], 'version' => '1.0'];
        $this->outline->setMetadata($metadata);
        $this->assertSame($metadata, $this->outline->getMetadata());
        
        $this->outline->setMetadata(null);
        $this->assertNull($this->outline->getMetadata());
    }

    public function test_is_published(): void
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

    public function test_estimated_hours_calculation(): void
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

    public function test_fluent_interface(): void
    {
        $course = $this->createMock(Course::class);
        
        $result = $this->outline
            ->setTitle('第一章 安全生产法律法规')
            ->setCourse($course)
            ->setLearningObjectives('掌握安全生产法律法规')
            ->setContentPoints('法律条文学习')
            ->setKeyDifficulties('实际应用')
            ->setAssessmentCriteria('考试80分以上')
            ->setReferences('安全生产法')
            ->setEstimatedMinutes(120)
            ->setSortNumber(1)
            ->setStatus('published')
            ->setMetadata(['version' => '1.0'])
            ->setCreatedBy('user123')
            ->setUpdatedBy('user456');
        
        $this->assertSame($this->outline, $result);
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

    public function test_status_values(): void
    {
        // 测试常见的状态值
        $statuses = ['draft', 'published', 'archived', 'reviewing'];
        
        foreach ($statuses as $status) {
            $this->outline->setStatus($status);
            $this->assertSame($status, $this->outline->getStatus());
            
            // 只有published状态返回true
            $this->assertSame($status === 'published', $this->outline->isPublished());
        }
    }

    public function test_estimated_minutes_edge_cases(): void
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

    public function test_metadata_complex_structure(): void
    {
        $complexMetadata = [
            'tags' => ['安全', '法律', '培训'],
            'version' => '2.1',
            'author' => '张老师',
            'difficulty' => 'intermediate',
            'prerequisites' => ['基础安全知识'],
            'resources' => [
                'videos' => ['video1.mp4', 'video2.mp4'],
                'documents' => ['doc1.pdf', 'doc2.pdf']
            ]
        ];
        
        $this->outline->setMetadata($complexMetadata);
        $this->assertSame($complexMetadata, $this->outline->getMetadata());
        
        // 测试可以访问嵌套数据
        $metadata = $this->outline->getMetadata();
        $this->assertSame(['安全', '法律', '培训'], $metadata['tags']);
        $this->assertSame('张老师', $metadata['author']);
        $this->assertSame(['video1.mp4', 'video2.mp4'], $metadata['resources']['videos']);
    }
} 