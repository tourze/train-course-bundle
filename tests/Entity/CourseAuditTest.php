<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;

/**
 * CourseAudit 实体测试
 *
 * 测试课程审核实体的基础属性、关联关系和业务方法
 *
 * @internal
 */
#[CoversClass(CourseAudit::class)]
final class CourseAuditTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CourseAudit();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'status' => ['status', 'test_value'],
            'auditType' => ['auditType', 'test_value'],
            'auditLevel' => ['auditLevel', 123],
            'priority' => ['priority', 123],
        ];
    }

    private CourseAudit $audit;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->audit = new CourseAudit();
    }

    public function testConstructInitializesProperly(): void
    {
        $audit = new CourseAudit();

        $this->assertNull($audit->getId());
        $this->assertSame('pending', $audit->getStatus()); // 默认值
        $this->assertSame('content', $audit->getAuditType()); // 默认值
        $this->assertSame(1, $audit->getAuditLevel()); // 默认值
        $this->assertSame(0, $audit->getPriority()); // 默认值
    }

    public function testCreatedByProperty(): void
    {
        $this->assertNull($this->audit->getCreatedBy());

        $this->audit->setCreatedBy('user123');
        $this->assertSame('user123', $this->audit->getCreatedBy());

        $this->audit->setCreatedBy(null);
        $this->assertNull($this->audit->getCreatedBy());
    }

    public function testUpdatedByProperty(): void
    {
        $this->assertNull($this->audit->getUpdatedBy());

        $this->audit->setUpdatedBy('user456');
        $this->assertSame('user456', $this->audit->getUpdatedBy());

        $this->audit->setUpdatedBy(null);
        $this->assertNull($this->audit->getUpdatedBy());
    }

    public function testCourseProperty(): void
    {
        $this->assertNull($this->audit->getCourse());

        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：CourseAudit实体与Course实体存在多对一关联关系，需要测试课程审核关联
         * 必要性：验证CourseAudit实体能正确存储和返回关联的Course对象引用，支持null值设置
         * 替代方案：可以使用真实的Course实体，但Mock对象更适合单元测试的隔离性原则
         */
        $course = $this->createMock(Course::class);
        $this->audit->setCourse($course);
        $this->assertSame($course, $this->audit->getCourse());

        $this->audit->setCourse(null);
        $this->assertNull($this->audit->getCourse());
    }

    public function testStatusProperty(): void
    {
        $this->assertSame('pending', $this->audit->getStatus()); // 默认值

        $this->audit->setStatus('approved');
        $this->assertSame('approved', $this->audit->getStatus());

        $this->audit->setStatus('rejected');
        $this->assertSame('rejected', $this->audit->getStatus());
    }

    public function testAuditTypeProperty(): void
    {
        $this->assertSame('content', $this->audit->getAuditType()); // 默认值

        $this->audit->setAuditType('quality');
        $this->assertSame('quality', $this->audit->getAuditType());

        $this->audit->setAuditType('compliance');
        $this->assertSame('compliance', $this->audit->getAuditType());
    }

    public function testAuditorProperty(): void
    {
        $this->assertNull($this->audit->getAuditor());

        $this->audit->setAuditor('张审核员');
        $this->assertSame('张审核员', $this->audit->getAuditor());

        $this->audit->setAuditor(null);
        $this->assertNull($this->audit->getAuditor());
    }

    public function testAuditCommentProperty(): void
    {
        $this->assertNull($this->audit->getAuditComment());

        $comment = '课程内容符合要求，建议通过';
        $this->audit->setAuditComment($comment);
        $this->assertSame($comment, $this->audit->getAuditComment());

        $this->audit->setAuditComment(null);
        $this->assertNull($this->audit->getAuditComment());
    }

    public function testAuditTimeProperty(): void
    {
        $this->assertNull($this->audit->getAuditTime());

        $auditTime = new \DateTimeImmutable('2024-01-15 10:30:00');
        $this->audit->setAuditTime($auditTime);
        $this->assertSame($auditTime, $this->audit->getAuditTime());

        $this->audit->setAuditTime(null);
        $this->assertNull($this->audit->getAuditTime());
    }

    public function testAuditLevelProperty(): void
    {
        $this->assertSame(1, $this->audit->getAuditLevel()); // 默认值

        $this->audit->setAuditLevel(2);
        $this->assertSame(2, $this->audit->getAuditLevel());

        $this->audit->setAuditLevel(0);
        $this->assertSame(0, $this->audit->getAuditLevel());
    }

    public function testPriorityProperty(): void
    {
        $this->assertSame(0, $this->audit->getPriority()); // 默认值

        $this->audit->setPriority(5);
        $this->assertSame(5, $this->audit->getPriority());

        $this->audit->setPriority(-1);
        $this->assertSame(-1, $this->audit->getPriority());
    }

    public function testDeadlineProperty(): void
    {
        $this->assertNull($this->audit->getDeadline());

        $deadline = new \DateTimeImmutable('2024-01-20 23:59:59');
        $this->audit->setDeadline($deadline);
        $this->assertSame($deadline, $this->audit->getDeadline());

        $this->audit->setDeadline(null);
        $this->assertNull($this->audit->getDeadline());
    }

    public function testAuditDataProperty(): void
    {
        $this->assertNull($this->audit->getAuditData());

        $auditData = ['score' => 85, 'issues' => ['minor formatting']];
        $this->audit->setAuditData($auditData);
        $this->assertSame($auditData, $this->audit->getAuditData());

        $this->audit->setAuditData(null);
        $this->assertNull($this->audit->getAuditData());
    }

    public function testMetadataProperty(): void
    {
        $this->assertNull($this->audit->getMetadata());

        $metadata = ['reviewer' => '张老师', 'department' => '安全部'];
        $this->audit->setMetadata($metadata);
        $this->assertSame($metadata, $this->audit->getMetadata());

        $this->audit->setMetadata(null);
        $this->assertNull($this->audit->getMetadata());
    }

    public function testIsApproved(): void
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

    public function testIsRejected(): void
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

    public function testIsPending(): void
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

    public function testIsOverdue(): void
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

    public function testGetStatusLabel(): void
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

    public function testGetAuditTypeLabel(): void
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

    public function testFluentInterface(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：测试setter方法需要一个Course对象来验证setCourse方法
         * 必要性：验证CourseAudit实体所有setter方法都能正确设置属性值
         * 替代方案：可以使用真实Course对象，但Mock对象更轻量且符合测试隔离原则
         */
        $course = $this->createMock(Course::class);
        $auditTime = new \DateTimeImmutable('2024-01-15 10:30:00');
        $deadline = new \DateTimeImmutable('2024-01-20 23:59:59');

        $this->audit->setCourse($course);
        $this->audit->setStatus('approved');
        $this->audit->setAuditType('quality');
        $this->audit->setAuditor('张审核员');
        $this->audit->setAuditComment('审核通过');
        $this->audit->setAuditTime($auditTime);
        $this->audit->setAuditLevel(2);
        $this->audit->setPriority(5);
        $this->audit->setDeadline($deadline);
        $this->audit->setAuditData(['score' => 90]);
        $this->audit->setMetadata(['reviewer' => '张老师']);
        $this->audit->setCreatedBy('user123');
        $this->audit->setUpdatedBy('user456');

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

    public function testAuditWorkflow(): void
    {
        // 模拟审核工作流
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：测试审核工作流程需要一个Course对象来模拟完整的审核业务场景
         * 必要性：验证CourseAudit实体在完整的审核工作流中的状态变化和业务逻辑
         * 替代方案：可以使用真实Course对象，但Mock对象能更好地控制测试场景，避免外部依赖
         */
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

    public function testComplexAuditData(): void
    {
        $complexAuditData = [
            'score' => 88,
            'criteria' => [
                'content_quality' => 90,
                'technical_accuracy' => 85,
                'presentation' => 90,
                'compliance' => 85,
            ],
            'issues' => [
                ['type' => 'minor', 'description' => '部分图片清晰度不够'],
                ['type' => 'suggestion', 'description' => '建议增加实例说明'],
            ],
            'recommendations' => [
                '优化视频画质',
                '增加互动环节',
            ],
        ];

        $this->audit->setAuditData($complexAuditData);
        $this->assertSame($complexAuditData, $this->audit->getAuditData());

        // 测试可以访问嵌套数据
        $auditData = $this->audit->getAuditData();
        $this->assertArrayHasKey('score', $auditData);
        $this->assertArrayHasKey('criteria', $auditData);
        $this->assertArrayHasKey('issues', $auditData);
        $this->assertArrayHasKey('recommendations', $auditData);

        // 验证数据结构完整性
        $this->assertSame(88, $auditData['score']);
        $this->assertSame(['content_quality' => 90, 'technical_accuracy' => 85, 'presentation' => 90, 'compliance' => 85], $auditData['criteria']);
        $this->assertSame([['type' => 'minor', 'description' => '部分图片清晰度不够'], ['type' => 'suggestion', 'description' => '建议增加实例说明']], $auditData['issues']);
        $this->assertSame(['优化视频画质', '增加互动环节'], $auditData['recommendations']);

        // 验证业务逻辑：分数应该在合理范围内
        $this->assertGreaterThanOrEqual(0, $auditData['score']);
        $this->assertLessThanOrEqual(100, $auditData['score']);

        // 验证评审标准结构
        $this->assertArrayHasKey('content_quality', $auditData['criteria']);
        $this->assertArrayHasKey('technical_accuracy', $auditData['criteria']);

        // 验证问题记录结构
        $this->assertCount(2, $auditData['issues']);
        $firstIssue = $auditData['issues'][0];
        $this->assertArrayHasKey('type', $firstIssue);
        $this->assertArrayHasKey('description', $firstIssue);
        $this->assertSame('部分图片清晰度不够', $firstIssue['description']);
        $this->assertContains($firstIssue['type'], ['minor', 'major', 'suggestion']);
    }

    public function testEdgeCases(): void
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
