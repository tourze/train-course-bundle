<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * Collect 实体单元测试
 */
class CollectTest extends TestCase
{
    private Collect $collect;

    protected function setUp(): void
    {
        $this->collect = new Collect();
    }

    public function test_getId_returnsNullByDefault(): void
    {
        $this->assertNull($this->collect->getId());
    }

    public function test_setAndGetCreatedBy_worksCorrectly(): void
    {
        $createdBy = 'user123';
        $result = $this->collect->setCreatedBy($createdBy);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($createdBy, $this->collect->getCreatedBy());
    }

    public function test_setAndGetUpdatedBy_worksCorrectly(): void
    {
        $updatedBy = 'user456';
        $result = $this->collect->setUpdatedBy($updatedBy);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($updatedBy, $this->collect->getUpdatedBy());
    }

    public function test_setAndGetUserId_worksCorrectly(): void
    {
        $userId = 'user789';
        $result = $this->collect->setUserId($userId);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($userId, $this->collect->getUserId());
    }

    public function test_setAndGetCourse_worksCorrectly(): void
    {
        $course = $this->createMock(Course::class);
        $result = $this->collect->setCourse($course);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($course, $this->collect->getCourse());
    }

    public function test_setAndGetStatus_worksCorrectly(): void
    {
        $this->assertSame('active', $this->collect->getStatus()); // 默认值
        
        $status = 'cancelled';
        $result = $this->collect->setStatus($status);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($status, $this->collect->getStatus());
    }

    public function test_setAndGetCollectGroup_worksCorrectly(): void
    {
        $collectGroup = '我的收藏';
        $result = $this->collect->setCollectGroup($collectGroup);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($collectGroup, $this->collect->getCollectGroup());
    }

    public function test_setAndGetNote_worksCorrectly(): void
    {
        $note = '这是一个很好的课程';
        $result = $this->collect->setNote($note);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($note, $this->collect->getNote());
    }

    public function test_setAndGetSortNumber_worksCorrectly(): void
    {
        $this->assertSame(0, $this->collect->getSortNumber()); // 默认值
        
        $sortNumber = 100;
        $result = $this->collect->setSortNumber($sortNumber);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($sortNumber, $this->collect->getSortNumber());
    }

    public function test_setAndGetIsTop_worksCorrectly(): void
    {
        $this->assertFalse($this->collect->isIsTop()); // 默认值
        
        $result = $this->collect->setIsTop(true);
        
        $this->assertSame($this->collect, $result);
        $this->assertTrue($this->collect->isIsTop());
        
        $this->collect->setIsTop(false);
        $this->assertFalse($this->collect->isIsTop());
    }

    public function test_setAndGetMetadata_worksCorrectly(): void
    {
        $metadata = ['key' => 'value', 'tags' => ['tag1', 'tag2']];
        $result = $this->collect->setMetadata($metadata);
        
        $this->assertSame($this->collect, $result);
        $this->assertSame($metadata, $this->collect->getMetadata());
    }

    public function test_setAndGetMetadata_withNull_worksCorrectly(): void
    {
        $this->collect->setMetadata(['test' => 'data']);
        $result = $this->collect->setMetadata(null);
        
        $this->assertSame($this->collect, $result);
        $this->assertNull($this->collect->getMetadata());
    }

    public function test_isActive_withActiveStatus_returnsTrue(): void
    {
        $this->collect->setStatus('active');
        $this->assertTrue($this->collect->isActive());
    }

    public function test_isActive_withCancelledStatus_returnsFalse(): void
    {
        $this->collect->setStatus('cancelled');
        $this->assertFalse($this->collect->isActive());
    }

    public function test_isActive_withHiddenStatus_returnsFalse(): void
    {
        $this->collect->setStatus('hidden');
        $this->assertFalse($this->collect->isActive());
    }

    public function test_getStatusLabel_withActiveStatus_returnsCorrectLabel(): void
    {
        $this->collect->setStatus('active');
        $this->assertSame('已收藏', $this->collect->getStatusLabel());
    }

    public function test_getStatusLabel_withCancelledStatus_returnsCorrectLabel(): void
    {
        $this->collect->setStatus('cancelled');
        $this->assertSame('已取消', $this->collect->getStatusLabel());
    }

    public function test_getStatusLabel_withHiddenStatus_returnsCorrectLabel(): void
    {
        $this->collect->setStatus('hidden');
        $this->assertSame('已隐藏', $this->collect->getStatusLabel());
    }

    public function test_getStatusLabel_withUnknownStatus_returnsDefaultLabel(): void
    {
        $this->collect->setStatus('unknown');
        $this->assertSame('未知状态', $this->collect->getStatusLabel());
    }

    public function test_defaultValues_areSetCorrectly(): void
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

    public function test_collectGroup_canBeNull(): void
    {
        $this->collect->setCollectGroup('测试分组');
        $this->assertSame('测试分组', $this->collect->getCollectGroup());
        
        $this->collect->setCollectGroup(null);
        $this->assertNull($this->collect->getCollectGroup());
    }

    public function test_note_canBeNull(): void
    {
        $this->collect->setNote('测试备注');
        $this->assertSame('测试备注', $this->collect->getNote());
        
        $this->collect->setNote(null);
        $this->assertNull($this->collect->getNote());
    }

    public function test_course_canBeNull(): void
    {
        $course = $this->createMock(Course::class);
        $this->collect->setCourse($course);
        $this->assertSame($course, $this->collect->getCourse());
        
        $this->collect->setCourse(null);
        $this->assertNull($this->collect->getCourse());
    }

    public function test_sortNumber_acceptsNegativeValues(): void
    {
        $this->collect->setSortNumber(-10);
        $this->assertSame(-10, $this->collect->getSortNumber());
    }

    public function test_sortNumber_acceptsLargeValues(): void
    {
        $this->collect->setSortNumber(999999);
        $this->assertSame(999999, $this->collect->getSortNumber());
    }

    public function test_metadata_acceptsComplexArray(): void
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

    public function test_metadata_acceptsEmptyArray(): void
    {
        $this->collect->setMetadata([]);
        $this->assertSame([], $this->collect->getMetadata());
    }
} 