<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * Collect 实体单元测试
 *
 * @internal
 */
#[CoversClass(Collect::class)]
final class CollectTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Collect();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'status' => ['status', 'test_value'],
            'sortNumber' => ['sortNumber', 123],
            'isTop' => ['isTop', true],
        ];
    }

    private Collect $collect;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->collect = new Collect();
    }

    public function testGetIdReturnsNullByDefault(): void
    {
        $this->assertNull($this->collect->getId());
    }

    public function testSetAndGetCreatedByWorksCorrectly(): void
    {
        $createdBy = 'user123';
        $this->collect->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $this->collect->getCreatedBy());
    }

    public function testSetAndGetUpdatedByWorksCorrectly(): void
    {
        $updatedBy = 'user456';
        $this->collect->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $this->collect->getUpdatedBy());
    }

    public function testSetAndGetUserIdWorksCorrectly(): void
    {
        $userId = 'user789';
        $this->collect->setUserId($userId);

        $this->assertSame($userId, $this->collect->getUserId());
    }

    public function testSetAndGetCourseWorksCorrectly(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：Collect实体与Course实体存在多对一关联关系，需要测试课程关联的设置和获取
         * 必要性：验证Collect实体能正确存储和返回关联的Course对象引用
         * 替代方案：可以使用真实的Course实体，但Mock对象更适合单元测试的隔离性要求
         */
        $course = $this->createMock(Course::class);
        $this->collect->setCourse($course);

        $this->assertSame($course, $this->collect->getCourse());
    }

    public function testSetAndGetStatusWorksCorrectly(): void
    {
        $this->assertSame('active', $this->collect->getStatus()); // 默认值

        $status = 'cancelled';
        $this->collect->setStatus($status);

        $this->assertSame($status, $this->collect->getStatus());
    }

    public function testSetAndGetCollectGroupWorksCorrectly(): void
    {
        $collectGroup = '我的收藏';
        $this->collect->setCollectGroup($collectGroup);

        $this->assertSame($collectGroup, $this->collect->getCollectGroup());
    }

    public function testSetAndGetNoteWorksCorrectly(): void
    {
        $note = '这是一个很好的课程';
        $this->collect->setNote($note);

        $this->assertSame($note, $this->collect->getNote());
    }

    public function testSetAndGetSortNumberWorksCorrectly(): void
    {
        $this->assertSame(0, $this->collect->getSortNumber()); // 默认值

        $sortNumber = 100;
        $this->collect->setSortNumber($sortNumber);

        $this->assertSame($sortNumber, $this->collect->getSortNumber());
    }

    public function testSetAndGetIsTopWorksCorrectly(): void
    {
        $this->assertFalse($this->collect->isIsTop()); // 默认值

        $this->collect->setIsTop(true);

        $this->assertTrue($this->collect->isIsTop());

        $this->collect->setIsTop(false);
        $this->assertFalse($this->collect->isIsTop());
    }

    public function testSetAndGetMetadataWorksCorrectly(): void
    {
        $metadata = ['key' => 'value', 'tags' => ['tag1', 'tag2']];
        $this->collect->setMetadata($metadata);

        $this->assertSame($metadata, $this->collect->getMetadata());
    }

    public function testSetAndGetMetadataWithNullWorksCorrectly(): void
    {
        $this->collect->setMetadata(['test' => 'data']);
        $this->collect->setMetadata(null);

        $this->assertNull($this->collect->getMetadata());
    }

    public function testIsActiveWithActiveStatusReturnsTrue(): void
    {
        $this->collect->setStatus('active');
        $this->assertTrue($this->collect->isActive());
    }

    public function testIsActiveWithCancelledStatusReturnsFalse(): void
    {
        $this->collect->setStatus('cancelled');
        $this->assertFalse($this->collect->isActive());
    }

    public function testIsActiveWithHiddenStatusReturnsFalse(): void
    {
        $this->collect->setStatus('hidden');
        $this->assertFalse($this->collect->isActive());
    }

    public function testGetStatusLabelWithActiveStatusReturnsCorrectLabel(): void
    {
        $this->collect->setStatus('active');
        $this->assertSame('已收藏', $this->collect->getStatusLabel());
    }

    public function testGetStatusLabelWithCancelledStatusReturnsCorrectLabel(): void
    {
        $this->collect->setStatus('cancelled');
        $this->assertSame('已取消', $this->collect->getStatusLabel());
    }

    public function testGetStatusLabelWithHiddenStatusReturnsCorrectLabel(): void
    {
        $this->collect->setStatus('hidden');
        $this->assertSame('已隐藏', $this->collect->getStatusLabel());
    }

    public function testGetStatusLabelWithUnknownStatusReturnsDefaultLabel(): void
    {
        $this->collect->setStatus('unknown');
        $this->assertSame('未知状态', $this->collect->getStatusLabel());
    }

    public function testDefaultValuesAreSetCorrectly(): void
    {
        $collect = new Collect();

        $this->assertSame('active', $collect->getStatus());
        $this->assertSame(0, $collect->getSortNumber());
        $this->assertFalse($collect->isIsTop());
        $this->assertNull($collect->getCollectGroup());
        $this->assertNull($collect->getNote());
        $this->assertNull($collect->getMetadata());
        $this->assertNull($collect->getUserId());
        $this->assertNull($collect->getCourse());
    }

    public function testCollectGroupCanBeNull(): void
    {
        $this->collect->setCollectGroup('测试分组');
        $this->assertSame('测试分组', $this->collect->getCollectGroup());

        $this->collect->setCollectGroup(null);
        $this->assertNull($this->collect->getCollectGroup());
    }

    public function testNoteCanBeNull(): void
    {
        $this->collect->setNote('测试备注');
        $this->assertSame('测试备注', $this->collect->getNote());

        $this->collect->setNote(null);
        $this->assertNull($this->collect->getNote());
    }

    public function testCourseCanBeNull(): void
    {
        /*
         * 使用具体的Course实体类创建Mock对象
         * 原因：测试Course关联属性的null值设置功能，需要先设置一个Course对象再置为null
         * 必要性：验证Collect实体的setCourse方法能接受null值并正确存储
         * 替代方案：可以使用真实的Course实体，但Mock对象更符合单元测试的轻量化要求
         */
        $course = $this->createMock(Course::class);
        $this->collect->setCourse($course);
        $this->assertSame($course, $this->collect->getCourse());

        $this->collect->setCourse(null);
        $this->assertNull($this->collect->getCourse());
    }

    public function testSortNumberAcceptsNegativeValues(): void
    {
        $this->collect->setSortNumber(-10);
        $this->assertSame(-10, $this->collect->getSortNumber());
    }

    public function testSortNumberAcceptsLargeValues(): void
    {
        $this->collect->setSortNumber(999999);
        $this->assertSame(999999, $this->collect->getSortNumber());
    }

    public function testMetadataAcceptsComplexArray(): void
    {
        $complexMetadata = [
            'user_preferences' => [
                'notification' => true,
                'auto_play' => false,
            ],
            'tags' => ['重要', '必学', '考试'],
            'custom_fields' => [
                'priority' => 'high',
                'deadline' => '2024-12-31',
            ],
        ];

        $this->collect->setMetadata($complexMetadata);
        $this->assertSame($complexMetadata, $this->collect->getMetadata());
    }

    public function testMetadataAcceptsEmptyArray(): void
    {
        $this->collect->setMetadata([]);
        $this->assertSame([], $this->collect->getMetadata());
    }
}
