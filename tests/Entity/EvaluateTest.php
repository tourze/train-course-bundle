<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * Evaluate 实体单元测试
 *
 * @internal
 */
#[CoversClass(Evaluate::class)]
final class EvaluateTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Evaluate();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'rating' => ['rating', 4],
            'status' => ['status', 'test_value'],
            'isAnonymous' => ['isAnonymous', true],
            'likeCount' => ['likeCount', 10],
            'replyCount' => ['replyCount', 5],
        ];
    }

    private Evaluate $evaluate;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->evaluate = new Evaluate();
    }

    public function testGetIdReturnsNullByDefault(): void
    {
        $this->assertNull($this->evaluate->getId());
    }

    public function testSetAndGetCreatedByWorksCorrectly(): void
    {
        $createdBy = 'user123';
        $this->evaluate->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $this->evaluate->getCreatedBy());
    }

    public function testSetAndGetUpdatedByWorksCorrectly(): void
    {
        $updatedBy = 'user456';
        $this->evaluate->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $this->evaluate->getUpdatedBy());
    }

    public function testSetAndGetUserIdWorksCorrectly(): void
    {
        $userId = 'user789';
        $this->evaluate->setUserId($userId);

        $this->assertSame($userId, $this->evaluate->getUserId());
    }

    public function testSetAndGetCourseWorksCorrectly(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：Evaluate实体与Course实体存在多对一关联关系，需要测试课程关联的设置和获取
         * 必要性：验证Evaluate实体能正确存储和返回关联的Course对象引用
         * 替代方案：可以使用真实的Course实体，但Mock对象更适合单元测试的隔离性要求
         */
        $course = $this->createMock(Course::class);
        $this->evaluate->setCourse($course);

        $this->assertSame($course, $this->evaluate->getCourse());
    }

    public function testSetAndGetRatingWorksCorrectly(): void
    {
        $this->assertSame(5, $this->evaluate->getRating()); // 默认值

        $rating = 4;
        $this->evaluate->setRating($rating);

        $this->assertSame($rating, $this->evaluate->getRating());
    }

    public function testSetRatingEnforcesMinimumValue(): void
    {
        $this->evaluate->setRating(0);
        $this->assertSame(1, $this->evaluate->getRating());

        $this->evaluate->setRating(-5);
        $this->assertSame(1, $this->evaluate->getRating());
    }

    public function testSetRatingEnforcesMaximumValue(): void
    {
        $this->evaluate->setRating(6);
        $this->assertSame(5, $this->evaluate->getRating());

        $this->evaluate->setRating(10);
        $this->assertSame(5, $this->evaluate->getRating());
    }

    public function testSetAndGetContentWorksCorrectly(): void
    {
        $content = '这是一个很好的课程，内容丰富，讲解清晰。';
        $this->evaluate->setContent($content);

        $this->assertSame($content, $this->evaluate->getContent());
    }

    public function testSetAndGetStatusWorksCorrectly(): void
    {
        $this->assertSame('published', $this->evaluate->getStatus()); // 默认值

        $status = 'pending';
        $this->evaluate->setStatus($status);

        $this->assertSame($status, $this->evaluate->getStatus());
    }

    public function testSetAndGetIsAnonymousWorksCorrectly(): void
    {
        $this->assertFalse($this->evaluate->isIsAnonymous()); // 默认值

        $this->evaluate->setIsAnonymous(true);

        $this->assertTrue($this->evaluate->isIsAnonymous());

        $this->evaluate->setIsAnonymous(false);
        $this->assertFalse($this->evaluate->isIsAnonymous());
    }

    public function testSetAndGetLikeCountWorksCorrectly(): void
    {
        $this->assertSame(0, $this->evaluate->getLikeCount()); // 默认值

        $likeCount = 10;
        $this->evaluate->setLikeCount($likeCount);

        $this->assertSame($likeCount, $this->evaluate->getLikeCount());
    }

    public function testSetLikeCountEnforcesMinimumValue(): void
    {
        $this->evaluate->setLikeCount(-5);
        $this->assertSame(0, $this->evaluate->getLikeCount());
    }

    public function testSetAndGetReplyCountWorksCorrectly(): void
    {
        $this->assertSame(0, $this->evaluate->getReplyCount()); // 默认值

        $replyCount = 5;
        $this->evaluate->setReplyCount($replyCount);

        $this->assertSame($replyCount, $this->evaluate->getReplyCount());
    }

    public function testSetReplyCountEnforcesMinimumValue(): void
    {
        $this->evaluate->setReplyCount(-3);
        $this->assertSame(0, $this->evaluate->getReplyCount());
    }

    public function testSetAndGetUserNicknameWorksCorrectly(): void
    {
        $userNickname = '张三';
        $this->evaluate->setUserNickname($userNickname);

        $this->assertSame($userNickname, $this->evaluate->getUserNickname());
    }

    public function testSetAndGetUserAvatarWorksCorrectly(): void
    {
        $userAvatar = '/uploads/avatar/user123.jpg';
        $this->evaluate->setUserAvatar($userAvatar);

        $this->assertSame($userAvatar, $this->evaluate->getUserAvatar());
    }

    public function testSetAndGetAuditTimeWorksCorrectly(): void
    {
        $auditTime = new \DateTimeImmutable('2024-01-15 10:30:00');
        $this->evaluate->setAuditTime($auditTime);

        $this->assertSame($auditTime, $this->evaluate->getAuditTime());
    }

    public function testSetAndGetAuditorWorksCorrectly(): void
    {
        $auditor = 'admin123';
        $this->evaluate->setAuditor($auditor);

        $this->assertSame($auditor, $this->evaluate->getAuditor());
    }

    public function testSetAndGetAuditCommentWorksCorrectly(): void
    {
        $auditComment = '评价内容符合规范，审核通过';
        $this->evaluate->setAuditComment($auditComment);

        $this->assertSame($auditComment, $this->evaluate->getAuditComment());
    }

    public function testSetAndGetMetadataWorksCorrectly(): void
    {
        $metadata = ['source' => 'mobile', 'version' => '1.0'];
        $this->evaluate->setMetadata($metadata);

        $this->assertSame($metadata, $this->evaluate->getMetadata());
    }

    public function testIsPublishedWithPublishedStatusReturnsTrue(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertTrue($this->evaluate->isPublished());
    }

    public function testIsPublishedWithPendingStatusReturnsFalse(): void
    {
        $this->evaluate->setStatus('pending');
        $this->assertFalse($this->evaluate->isPublished());
    }

    public function testIsPendingWithPendingStatusReturnsTrue(): void
    {
        $this->evaluate->setStatus('pending');
        $this->assertTrue($this->evaluate->isPending());
    }

    public function testIsPendingWithPublishedStatusReturnsFalse(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertFalse($this->evaluate->isPending());
    }

    public function testIsRejectedWithRejectedStatusReturnsTrue(): void
    {
        $this->evaluate->setStatus('rejected');
        $this->assertTrue($this->evaluate->isRejected());
    }

    public function testIsRejectedWithPublishedStatusReturnsFalse(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertFalse($this->evaluate->isRejected());
    }

    public function testGetStatusLabelWithPublishedStatusReturnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('published');
        $this->assertSame('已发布', $this->evaluate->getStatusLabel());
    }

    public function testGetStatusLabelWithPendingStatusReturnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('pending');
        $this->assertSame('待审核', $this->evaluate->getStatusLabel());
    }

    public function testGetStatusLabelWithRejectedStatusReturnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('rejected');
        $this->assertSame('已拒绝', $this->evaluate->getStatusLabel());
    }

    public function testGetStatusLabelWithHiddenStatusReturnsCorrectLabel(): void
    {
        $this->evaluate->setStatus('hidden');
        $this->assertSame('已隐藏', $this->evaluate->getStatusLabel());
    }

    public function testGetStatusLabelWithUnknownStatusReturnsDefaultLabel(): void
    {
        $this->evaluate->setStatus('unknown');
        $this->assertSame('未知状态', $this->evaluate->getStatusLabel());
    }

    public function testGetRatingLabelReturnsCorrectLabels(): void
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

    public function testIncrementLikeCountWorksCorrectly(): void
    {
        $this->evaluate->setLikeCount(5);
        $this->evaluate->incrementLikeCount();

        $this->assertSame(6, $this->evaluate->getLikeCount());
    }

    public function testDecrementLikeCountWorksCorrectly(): void
    {
        $this->evaluate->setLikeCount(5);
        $this->evaluate->decrementLikeCount();

        $this->assertSame(4, $this->evaluate->getLikeCount());
    }

    public function testDecrementLikeCountDoesNotGoBelowZero(): void
    {
        $this->evaluate->setLikeCount(0);
        $this->evaluate->decrementLikeCount();

        $this->assertSame(0, $this->evaluate->getLikeCount());
    }

    public function testIncrementReplyCountWorksCorrectly(): void
    {
        $this->evaluate->setReplyCount(3);
        $this->evaluate->incrementReplyCount();

        $this->assertSame(4, $this->evaluate->getReplyCount());
    }

    public function testDecrementReplyCountWorksCorrectly(): void
    {
        $this->evaluate->setReplyCount(3);
        $this->evaluate->decrementReplyCount();

        $this->assertSame(2, $this->evaluate->getReplyCount());
    }

    public function testDecrementReplyCountDoesNotGoBelowZero(): void
    {
        $this->evaluate->setReplyCount(0);
        $this->evaluate->decrementReplyCount();

        $this->assertSame(0, $this->evaluate->getReplyCount());
    }

    public function testGetDisplayUserNameWithAnonymousReturnsAnonymousUser(): void
    {
        $this->evaluate->setIsAnonymous(true);
        $this->evaluate->setUserNickname('张三');
        $this->evaluate->setUserId('user123456');

        $this->assertSame('匿名用户', $this->evaluate->getDisplayUserName());
    }

    public function testGetDisplayUserNameWithNicknameReturnsNickname(): void
    {
        $this->evaluate->setIsAnonymous(false);
        $this->evaluate->setUserNickname('张三');
        $this->evaluate->setUserId('user123456');

        $this->assertSame('张三', $this->evaluate->getDisplayUserName());
    }

    public function testGetDisplayUserNameWithoutNicknameReturnsUserIdSuffix(): void
    {
        $this->evaluate->setIsAnonymous(false);
        $this->evaluate->setUserNickname(null);
        $this->evaluate->setUserId('user123456');

        $this->assertSame('用户3456', $this->evaluate->getDisplayUserName());
    }

    public function testGetDisplayUserNameWithShortUserIdReturnsFullUserId(): void
    {
        $this->evaluate->setIsAnonymous(false);
        $this->evaluate->setUserNickname(null);
        $this->evaluate->setUserId('123');

        $this->assertSame('用户123', $this->evaluate->getDisplayUserName());
    }

    public function testDefaultValuesAreSetCorrectly(): void
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

    public function testNullableFieldsCanBeSetToNull(): void
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

        $this->evaluate->setAuditTime(new \DateTimeImmutable());
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
