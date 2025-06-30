<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;

/**
 * CourseAudit 实体测试
 *
 * 测试课程审核实体的基础属性、关联关系和业务方法
 */
class CourseAuditTest extends TestCase
{
    private CourseAudit $audit;

    protected function setUp(): void
    {
        $this->audit = new CourseAudit();
    }

    public function test_construct_initializes_properly(): void
    {
        $audit = new CourseAudit();
        
        $this->assertNull($audit->getId());
        $this->assertSame('pending', $audit->getStatus()); // 默认值
        $this->assertSame('content', $audit->getAuditType()); // 默认值
        $this->assertSame(1, $audit->getAuditLevel()); // 默认值
        $this->assertSame(0, $audit->getPriority()); // 默认值
    }

    public function test_created_by_property(): void
    {
        $this->assertNull($this->audit->getCreatedBy());
        
        $this->audit->setCreatedBy('user123');
        $this->assertSame('user123', $this->audit->getCreatedBy());
        
        $this->audit->setCreatedBy(null);
        $this->assertNull($this->audit->getCreatedBy());
    }

    public function test_updated_by_property(): void
    {
        $this->assertNull($this->audit->getUpdatedBy());
        
        $this->audit->setUpdatedBy('user456');
        $this->assertSame('user456', $this->audit->getUpdatedBy());
        
        $this->audit->setUpdatedBy(null);
        $this->assertNull($this->audit->getUpdatedBy());
    }

    public function test_course_property(): void
    {
        $this->assertNull($this->audit->getCourse());
        
        $course = $this->createMock(Course::class);
        $this->audit->setCourse($course);
        $this->assertSame($course, $this->audit->getCourse());
        
        $this->audit->setCourse(null);
        $this->assertNull($this->audit->getCourse());
    }

    public function test_status_property(): void
    {
        $this->assertSame('pending', $this->audit->getStatus()); // 默认值
        
        $this->audit->setStatus('approved');
        $this->assertSame('approved', $this->audit->getStatus());
        
        $this->audit->setStatus('rejected');
        $this->assertSame('rejected', $this->audit->getStatus());
    }

    public function test_audit_type_property(): void
    {
        $this->assertSame('content', $this->audit->getAuditType()); // 默认值
        
        $this->audit->setAuditType('quality');
        $this->assertSame('quality', $this->audit->getAuditType());
        
        $this->audit->setAuditType('compliance');
        $this->assertSame('compliance', $this->audit->getAuditType());
    }

    public function test_auditor_property(): void
    {
        $this->assertNull($this->audit->getAuditor());
        
        $this->audit->setAuditor('张审核员');
        $this->assertSame('张审核员', $this->audit->getAuditor());
        
        $this->audit->setAuditor(null);
        $this->assertNull($this->audit->getAuditor());
    }

    public function test_audit_comment_property(): void
    {
        $this->assertNull($this->audit->getAuditComment());
        
        $comment = '课程内容符合要求，建议通过';
        $this->audit->setAuditComment($comment);
        $this->assertSame($comment, $this->audit->getAuditComment());
        
        $this->audit->setAuditComment(null);
        $this->assertNull($this->audit->getAuditComment());
    }

    public function test_audit_time_property(): void
    {
        $this->assertNull($this->audit->getAuditTime());
        
        $auditTime = new \DateTimeImmutable('2024-01-15 10:30:00');
        $this->audit->setAuditTime($auditTime);
        $this->assertSame($auditTime, $this->audit->getAuditTime());
        
        $this->audit->setAuditTime(null);
        $this->assertNull($this->audit->getAuditTime());
    }

    public function test_audit_level_property(): void
    {
        $this->assertSame(1, $this->audit->getAuditLevel()); // 默认值
        
        $this->audit->setAuditLevel(2);
        $this->assertSame(2, $this->audit->getAuditLevel());
        
        $this->audit->setAuditLevel(0);
        $this->assertSame(0, $this->audit->getAuditLevel());
    }

    public function test_priority_property(): void
    {
        $this->assertSame(0, $this->audit->getPriority()); // 默认值
        
        $this->audit->setPriority(5);
        $this->assertSame(5, $this->audit->getPriority());
        
        $this->audit->setPriority(-1);
        $this->assertSame(-1, $this->audit->getPriority());
    }

    public function test_deadline_property(): void
    {
        $this->assertNull($this->audit->getDeadline());
        
        $deadline = new \DateTimeImmutable('2024-01-20 23:59:59');
        $this->audit->setDeadline($deadline);
        $this->assertSame($deadline, $this->audit->getDeadline());
        
        $this->audit->setDeadline(null);
        $this->assertNull($this->audit->getDeadline());
    }

    public function test_audit_data_property(): void
    {
        $this->assertNull($this->audit->getAuditData());
        
        $auditData = ['score' => 85, 'issues' => ['minor formatting']];
        $this->audit->setAuditData($auditData);
        $this->assertSame($auditData, $this->audit->getAuditData());
        
        $this->audit->setAuditData(null);
        $this->assertNull($this->audit->getAuditData());
    }

    public function test_metadata_property(): void
    {
        $this->assertNull($this->audit->getMetadata());
        
        $metadata = ['reviewer' => '张老师', 'department' => '安全部'];
        $this->audit->setMetadata($metadata);
        $this->assertSame($metadata, $this->audit->getMetadata());
        
        $this->audit->setMetadata(null);
        $this->assertNull($this->audit->getMetadata());
    }

    public function test_is_approved(): void
    {
        // 默认状态为pending，不是approved
        $this->assertFalse($this->audit->isApproved());
        
        // 设置为approved状态
        $this->audit->setStatus('approved');
        $this->assertTrue($this->audit->isApproved());
        
        // 设置为其他状态
        $this->audit->setStatus('rejected');
        $this->assertFalse($this->audit->isApproved());
        
        $this->audit->setStatus('pending');
        $this->assertFalse($this->audit->isApproved());
    }

    public function test_is_rejected(): void
    {
        // 默认状态为pending，不是rejected
        $this->assertFalse($this->audit->isRejected());
        
        // 设置为rejected状态
        $this->audit->setStatus('rejected');
        $this->assertTrue($this->audit->isRejected());
        
        // 设置为其他状态
        $this->audit->setStatus('approved');
        $this->assertFalse($this->audit->isRejected());
        
        $this->audit->setStatus('pending');
        $this->assertFalse($this->audit->isRejected());
    }

    public function test_is_pending(): void
    {
        // 默认状态为pending
        $this->assertTrue($this->audit->isPending());
        
        // 设置为其他状态
        $this->audit->setStatus('approved');
        $this->assertFalse($this->audit->isPending());
        
        $this->audit->setStatus('rejected');
        $this->assertFalse($this->audit->isPending());
        
        // 重新设置为pending
        $this->audit->setStatus('pending');
        $this->assertTrue($this->audit->isPending());
    }

    public function test_is_overdue(): void
    {
        // 没有截止时间，不会超时
        $this->assertFalse($this->audit->isOverdue());
        
        // 设置未来的截止时间，不会超时
        $futureDeadline = new \DateTimeImmutable('+1 day');
        $this->audit->setDeadline($futureDeadline);
        $this->assertFalse($this->audit->isOverdue());
        
        // 设置过去的截止时间，但状态不是pending，不算超时
        $pastDeadline = new \DateTimeImmutable('-1 day');
        $this->audit->setDeadline($pastDeadline);
        $this->audit->setStatus('approved');
        $this->assertFalse($this->audit->isOverdue());
        
        // 设置过去的截止时间，状态是pending，算超时
        $this->audit->setStatus('pending');
        $this->assertTrue($this->audit->isOverdue());
    }

    public function test_get_status_label(): void
    {
        // 测试各种状态的中文标签
        $statusLabels = [
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝',
            'in_progress' => '审核中',
            'unknown' => '未知状态', // 未知状态的默认值
        ];
        
        foreach ($statusLabels as $status => $expectedLabel) {
            $this->audit->setStatus($status);
            $this->assertSame($expectedLabel, $this->audit->getStatusLabel());
        }
    }

    public function test_get_audit_type_label(): void
    {
        // 测试各种审核类型的中文标签
        $typeLabels = [
            'content' => '内容审核',
            'quality' => '质量审核',
            'compliance' => '合规审核',
            'final' => '终审',
            'unknown' => '其他审核', // 未知类型的默认值
        ];
        
        foreach ($typeLabels as $type => $expectedLabel) {
            $this->audit->setAuditType($type);
            $this->assertSame($expectedLabel, $this->audit->getAuditTypeLabel());
        }
    }

    public function test_fluent_interface(): void
    {
        $course = $this->createMock(Course::class);
        $auditTime = new \DateTimeImmutable('2024-01-15 10:30:00');
        $deadline = new \DateTimeImmutable('2024-01-20 23:59:59');
        
        $result = $this->audit
            ->setCourse($course)
            ->setStatus('approved')
            ->setAuditType('quality')
            ->setAuditor('张审核员')
            ->setAuditComment('审核通过')
            ->setAuditTime($auditTime)
            ->setAuditLevel(2)
            ->setPriority(5)
            ->setDeadline($deadline)
            ->setAuditData(['score' => 90])
            ->setMetadata(['reviewer' => '张老师'])
            ->setCreatedBy('user123')
            ->setUpdatedBy('user456');
        
        $this->assertSame($this->audit, $result);
        $this->assertSame($course, $this->audit->getCourse());
        $this->assertSame('approved', $this->audit->getStatus());
        $this->assertSame('quality', $this->audit->getAuditType());
        $this->assertSame('张审核员', $this->audit->getAuditor());
        $this->assertSame('审核通过', $this->audit->getAuditComment());
        $this->assertSame($auditTime, $this->audit->getAuditTime());
        $this->assertSame(2, $this->audit->getAuditLevel());
        $this->assertSame(5, $this->audit->getPriority());
        $this->assertSame($deadline, $this->audit->getDeadline());
        $this->assertSame(['score' => 90], $this->audit->getAuditData());
        $this->assertSame(['reviewer' => '张老师'], $this->audit->getMetadata());
        $this->assertSame('user123', $this->audit->getCreatedBy());
        $this->assertSame('user456', $this->audit->getUpdatedBy());
    }

    public function test_audit_workflow(): void
    {
        // 模拟审核工作流
        $course = $this->createMock(Course::class);
        
        // 1. 创建审核记录
        $this->audit->setCourse($course);
        $this->audit->setAuditType('content');
        $this->audit->setAuditLevel(1);
        $this->audit->setDeadline(new \DateTimeImmutable('+7 days'));
        
        $this->assertTrue($this->audit->isPending());
        $this->assertFalse($this->audit->isApproved());
        $this->assertFalse($this->audit->isRejected());
        $this->assertFalse($this->audit->isOverdue());
        
        // 2. 分配审核员
        $this->audit->setAuditor('张审核员');
        $this->audit->setStatus('in_progress');
        
        $this->assertFalse($this->audit->isPending());
        $this->assertSame('审核中', $this->audit->getStatusLabel());
        
        // 3. 完成审核
        $this->audit->setStatus('approved');
        $this->audit->setAuditComment('课程内容符合要求');
        $this->audit->setAuditTime(new \DateTimeImmutable());
        $this->audit->setAuditData(['score' => 85, 'issues' => []]);
        
        $this->assertTrue($this->audit->isApproved());
        $this->assertFalse($this->audit->isPending());
        $this->assertFalse($this->audit->isRejected());
        $this->assertSame('已通过', $this->audit->getStatusLabel());
    }

    public function test_complex_audit_data(): void
    {
        $complexAuditData = [
            'score' => 88,
            'criteria' => [
                'content_quality' => 90,
                'technical_accuracy' => 85,
                'presentation' => 90,
                'compliance' => 85
            ],
            'issues' => [
                ['type' => 'minor', 'description' => '部分图片清晰度不够'],
                ['type' => 'suggestion', 'description' => '建议增加实例说明']
            ],
            'recommendations' => [
                '优化视频画质',
                '增加互动环节'
            ]
        ];
        
        $this->audit->setAuditData($complexAuditData);
        $this->assertSame($complexAuditData, $this->audit->getAuditData());
        
        // 测试可以访问嵌套数据
        $auditData = $this->audit->getAuditData();
        $this->assertSame(88, $auditData['score']);
        $this->assertSame(90, $auditData['criteria']['content_quality']);
        $this->assertCount(2, $auditData['issues']);
        $this->assertSame('部分图片清晰度不够', $auditData['issues'][0]['description']);
    }

    public function test_edge_cases(): void
    {
        // 测试边界值
        $this->audit->setAuditLevel(0);
        $this->assertSame(0, $this->audit->getAuditLevel());
        
        $this->audit->setAuditLevel(999);
        $this->assertSame(999, $this->audit->getAuditLevel());
        
        $this->audit->setPriority(-999);
        $this->assertSame(-999, $this->audit->getPriority());
        
        $this->audit->setPriority(999);
        $this->assertSame(999, $this->audit->getPriority());
        
        // 测试空字符串
        $this->audit->setAuditor('');
        $this->assertSame('', $this->audit->getAuditor());
        
        $this->audit->setAuditComment('');
        $this->assertSame('', $this->audit->getAuditComment());
    }
} 