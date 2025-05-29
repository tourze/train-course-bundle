<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * Evaluate 实体单元测试
 */
class EvaluateTest extends TestCase
{
    private Evaluate $evaluate;

    protected function setUp(): void
    {
        $this->evaluate = new Evaluate();
    }

    public function test_getId_returnsNullByDefault(): void
    {
        $this->assertNull($this->evaluate->getId());
    }

    public function test_setAndGetCreatedBy_worksCorrectly(): void
    {
        $createdBy = 'user123';
        $result = $this->evaluate->setCreatedBy($createdBy);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($createdBy, $this->evaluate->getCreatedBy());
    }

    public function test_setAndGetUpdatedBy_worksCorrectly(): void
    {
        $updatedBy = 'user456';
        $result = $this->evaluate->setUpdatedBy($updatedBy);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($updatedBy, $this->evaluate->getUpdatedBy());
    }

    public function test_setAndGetUserId_worksCorrectly(): void
    {
        $userId = 'user789';
        $result = $this->evaluate->setUserId($userId);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($userId, $this->evaluate->getUserId());
    }

    public function test_setAndGetCourse_worksCorrectly(): void
    {
        $course = $this->createMock(Course::class);
        $result = $this->evaluate->setCourse($course);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($course, $this->evaluate->getCourse());
    }

    public function test_setAndGetRating_worksCorrectly(): void
    {
        $this->assertSame(5, $this->evaluate->getRating()); // 默认值
        
        $rating = 4;
        $result = $this->evaluate->setRating($rating);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($rating, $this->evaluate->getRating());
    }

    public function test_setRating_enforcesMinimumValue(): void
    {
        $this->evaluate->setRating(0);
        $this->assertSame(1, $this->evaluate->getRating());
        
        $this->evaluate->setRating(-5);
        $this->assertSame(1, $this->evaluate->getRating());
    }

    public function test_setRating_enforcesMaximumValue(): void
    {
        $this->evaluate->setRating(6);
        $this->assertSame(5, $this->evaluate->getRating());
        
        $this->evaluate->setRating(10);
        $this->assertSame(5, $this->evaluate->getRating());
    }

    public function test_setAndGetContent_worksCorrectly(): void
    {
        $content = '这是一个很好的课程，内容丰富，讲解清晰。';
        $result = $this->evaluate->setContent($content);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($content, $this->evaluate->getContent());
    }

    public function test_setAndGetStatus_worksCorrectly(): void
    {
        $this->assertSame('published', $this->evaluate->getStatus()); // 默认值
        
        $status = 'pending';
        $result = $this->evaluate->setStatus($status);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($status, $this->evaluate->getStatus());
    }

    public function test_setAndGetIsAnonymous_worksCorrectly(): void
    {
        $this->assertFalse($this->evaluate->isIsAnonymous()); // 默认值
        
        $result = $this->evaluate->setIsAnonymous(true);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertTrue($this->evaluate->isIsAnonymous());
        
        $this->evaluate->setIsAnonymous(false);
        $this->assertFalse($this->evaluate->isIsAnonymous());
    }

    public function test_setAndGetLikeCount_worksCorrectly(): void
    {
        $this->assertSame(0, $this->evaluate->getLikeCount()); // 默认值
        
        $likeCount = 10;
        $result = $this->evaluate->setLikeCount($likeCount);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($likeCount, $this->evaluate->getLikeCount());
    }

    public function test_setLikeCount_enforcesMinimumValue(): void
    {
        $this->evaluate->setLikeCount(-5);
        $this->assertSame(0, $this->evaluate->getLikeCount());
    }

    public function test_setAndGetReplyCount_worksCorrectly(): void
    {
        $this->assertSame(0, $this->evaluate->getReplyCount()); // 默认值
        
        $replyCount = 5;
        $result = $this->evaluate->setReplyCount($replyCount);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($replyCount, $this->evaluate->getReplyCount());
    }

    public function test_setReplyCount_enforcesMinimumValue(): void
    {
        $this->evaluate->setReplyCount(-3);
        $this->assertSame(0, $this->evaluate->getReplyCount());
    }

    public function test_setAndGetUserNickname_worksCorrectly(): void
    {
        $userNickname = '张三';
        $result = $this->evaluate->setUserNickname($userNickname);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($userNickname, $this->evaluate->getUserNickname());
    }

    public function test_setAndGetUserAvatar_worksCorrectly(): void
    {
        $userAvatar = '/uploads/avatar/user123.jpg';
        $result = $this->evaluate->setUserAvatar($userAvatar);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($userAvatar, $this->evaluate->getUserAvatar());
    }

    public function test_setAndGetAuditTime_worksCorrectly(): void
    {
        $auditTime = new \DateTime('2024-01-15 10:30:00');
        $result = $this->evaluate->setAuditTime($auditTime);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($auditTime, $this->evaluate->getAuditTime());
    }

    public function test_setAndGetAuditor_worksCorrectly(): void
    {
        $auditor = 'admin123';
        $result = $this->evaluate->setAuditor($auditor);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($auditor, $this->evaluate->getAuditor());
    }

    public function test_setAndGetAuditComment_worksCorrectly(): void
    {
        $auditComment = '评价内容符合规范，审核通过';
        $result = $this->evaluate->setAuditComment($auditComment);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($auditComment, $this->evaluate->getAuditComment());
    }

    public function test_setAndGetMetadata_worksCorrectly(): void
    {
        $metadata = ['source' => 'mobile', 'version' => '1.0'];
        $result = $this->evaluate->setMetadata($metadata);
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame($metadata, $this->evaluate->getMetadata());
    }

    public function test_isPublished_withPublishedStatus_returnsTrue(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertTrue($this->evaluate->isPublished());
    }

    public function test_isPublished_withPendingStatus_returnsFalse(): void
    {
        $this->evaluate->setStatus('pending');
        $this->assertFalse($this->evaluate->isPublished());
    }

    public function test_isPending_withPendingStatus_returnsTrue(): void
    {
        $this->evaluate->setStatus('pending');
        $this->assertTrue($this->evaluate->isPending());
    }

    public function test_isPending_withPublishedStatus_returnsFalse(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertFalse($this->evaluate->isPending());
    }

    public function test_isRejected_withRejectedStatus_returnsTrue(): void
    {
        $this->evaluate->setStatus('rejected');
        $this->assertTrue($this->evaluate->isRejected());
    }

    public function test_isRejected_withPublishedStatus_returnsFalse(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertFalse($this->evaluate->isRejected());
    }

    public function test_getStatusLabel_withPublishedStatus_returnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertSame('已发布', $this->evaluate->getStatusLabel());
    }

    public function test_getStatusLabel_withPendingStatus_returnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('pending');
        $this->assertSame('待审核', $this->evaluate->getStatusLabel());
    }

    public function test_getStatusLabel_withRejectedStatus_returnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('rejected');
        $this->assertSame('已拒绝', $this->evaluate->getStatusLabel());
    }

    public function test_getStatusLabel_withHiddenStatus_returnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('hidden');
        $this->assertSame('已隐藏', $this->evaluate->getStatusLabel());
    }

    public function test_getStatusLabel_withUnknownStatus_returnsDefaultLabel(): void
    {
        $this->evaluate->setStatus('unknown');
        $this->assertSame('未知状态', $this->evaluate->getStatusLabel());
    }

    public function test_getRatingLabel_returnsCorrectLabels(): void
    {
        $this->evaluate->setRating(1);
        $this->assertSame('很差', $this->evaluate->getRatingLabel());
        
        $this->evaluate->setRating(2);
        $this->assertSame('较差', $this->evaluate->getRatingLabel());
        
        $this->evaluate->setRating(3);
        $this->assertSame('一般', $this->evaluate->getRatingLabel());
        
        $this->evaluate->setRating(4);
        $this->assertSame('较好', $this->evaluate->getRatingLabel());
        
        $this->evaluate->setRating(5);
        $this->assertSame('很好', $this->evaluate->getRatingLabel());
    }

    public function test_incrementLikeCount_worksCorrectly(): void
    {
        $this->evaluate->setLikeCount(5);
        $result = $this->evaluate->incrementLikeCount();
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame(6, $this->evaluate->getLikeCount());
    }

    public function test_decrementLikeCount_worksCorrectly(): void
    {
        $this->evaluate->setLikeCount(5);
        $result = $this->evaluate->decrementLikeCount();
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame(4, $this->evaluate->getLikeCount());
    }

    public function test_decrementLikeCount_doesNotGoBelowZero(): void
    {
        $this->evaluate->setLikeCount(0);
        $this->evaluate->decrementLikeCount();
        
        $this->assertSame(0, $this->evaluate->getLikeCount());
    }

    public function test_incrementReplyCount_worksCorrectly(): void
    {
        $this->evaluate->setReplyCount(3);
        $result = $this->evaluate->incrementReplyCount();
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame(4, $this->evaluate->getReplyCount());
    }

    public function test_decrementReplyCount_worksCorrectly(): void
    {
        $this->evaluate->setReplyCount(3);
        $result = $this->evaluate->decrementReplyCount();
        
        $this->assertSame($this->evaluate, $result);
        $this->assertSame(2, $this->evaluate->getReplyCount());
    }

    public function test_decrementReplyCount_doesNotGoBelowZero(): void
    {
        $this->evaluate->setReplyCount(0);
        $this->evaluate->decrementReplyCount();
        
        $this->assertSame(0, $this->evaluate->getReplyCount());
    }

    public function test_getDisplayUserName_withAnonymous_returnsAnonymousUser(): void
    {
        $this->evaluate->setIsAnonymous(true);
        $this->evaluate->setUserNickname('张三');
        $this->evaluate->setUserId('user123456');
        
        $this->assertSame('匿名用户', $this->evaluate->getDisplayUserName());
    }

    public function test_getDisplayUserName_withNickname_returnsNickname(): void
    {
        $this->evaluate->setIsAnonymous(false);
        $this->evaluate->setUserNickname('张三');
        $this->evaluate->setUserId('user123456');
        
        $this->assertSame('张三', $this->evaluate->getDisplayUserName());
    }

    public function test_getDisplayUserName_withoutNickname_returnsUserIdSuffix(): void
    {
        $this->evaluate->setIsAnonymous(false);
        $this->evaluate->setUserNickname(null);
        $this->evaluate->setUserId('user123456');
        
        $this->assertSame('用户3456', $this->evaluate->getDisplayUserName());
    }

    public function test_getDisplayUserName_withShortUserId_returnsFullUserId(): void
    {
        $this->evaluate->setIsAnonymous(false);
        $this->evaluate->setUserNickname(null);
        $this->evaluate->setUserId('123');
        
        $this->assertSame('用户123', $this->evaluate->getDisplayUserName());
    }

    public function test_defaultValues_areSetCorrectly(): void
    {
        $evaluate = new Evaluate();
        
        $this->assertSame(5, $evaluate->getRating());
        $this->assertSame('published', $evaluate->getStatus());
        $this->assertFalse($evaluate->isIsAnonymous());
        $this->assertSame(0, $evaluate->getLikeCount());
        $this->assertSame(0, $evaluate->getReplyCount());
        $this->assertNull($evaluate->getContent());
        $this->assertNull($evaluate->getUserNickname());
        $this->assertNull($evaluate->getUserAvatar());
        $this->assertNull($evaluate->getAuditTime());
        $this->assertNull($evaluate->getAuditor());
        $this->assertNull($evaluate->getAuditComment());
        $this->assertNull($evaluate->getMetadata());
    }

    public function test_nullableFields_canBeSetToNull(): void
    {
        $this->evaluate->setContent('测试内容');
        $this->evaluate->setContent(null);
        $this->assertNull($this->evaluate->getContent());
        
        $this->evaluate->setUserNickname('测试昵称');
        $this->evaluate->setUserNickname(null);
        $this->assertNull($this->evaluate->getUserNickname());
        
        $this->evaluate->setUserAvatar('/avatar.jpg');
        $this->evaluate->setUserAvatar(null);
        $this->assertNull($this->evaluate->getUserAvatar());
        
        $this->evaluate->setAuditTime(new \DateTime());
        $this->evaluate->setAuditTime(null);
        $this->assertNull($this->evaluate->getAuditTime());
        
        $this->evaluate->setAuditor('admin');
        $this->evaluate->setAuditor(null);
        $this->assertNull($this->evaluate->getAuditor());
        
        $this->evaluate->setAuditComment('审核意见');
        $this->evaluate->setAuditComment(null);
        $this->assertNull($this->evaluate->getAuditComment());
        
        $this->evaluate->setMetadata(['key' => 'value']);
        $this->evaluate->setMetadata(null);
        $this->assertNull($this->evaluate->getMetadata());
    }
} 