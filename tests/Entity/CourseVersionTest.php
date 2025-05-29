<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseVersion;

/**
 * CourseVersion 实体单元测试
 */
class CourseVersionTest extends TestCase
{
    private CourseVersion $version;

    protected function setUp(): void
    {
        $this->version = new CourseVersion();
    }

    public function test_getId_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getId());
    }

    public function test_setAndGetCreatedBy_worksCorrectly(): void
    {
        $createdBy = 'user123';
        $result = $this->version->setCreatedBy($createdBy);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($createdBy, $this->version->getCreatedBy());
    }

    public function test_setAndGetUpdatedBy_worksCorrectly(): void
    {
        $updatedBy = 'user456';
        $result = $this->version->setUpdatedBy($updatedBy);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($updatedBy, $this->version->getUpdatedBy());
    }

    public function test_setAndGetCourse_worksCorrectly(): void
    {
        $course = new Course();
        $result = $this->version->setCourse($course);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($course, $this->version->getCourse());
    }

    public function test_getCourse_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getCourse());
    }

    public function test_setAndGetVersion_worksCorrectly(): void
    {
        $versionNumber = 'v2.1.0';
        $result = $this->version->setVersion($versionNumber);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($versionNumber, $this->version->getVersion());
    }

    public function test_getVersion_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getVersion());
    }

    public function test_setAndGetTitle_worksCorrectly(): void
    {
        $title = '安全生产培训课程 v2.1';
        $result = $this->version->setTitle($title);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($title, $this->version->getTitle());
    }

    public function test_getTitle_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getTitle());
    }

    public function test_setAndGetDescription_worksCorrectly(): void
    {
        $description = '本版本增加了新的安全案例分析和互动练习';
        $result = $this->version->setDescription($description);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($description, $this->version->getDescription());
    }

    public function test_getDescription_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getDescription());
    }

    public function test_setAndGetChangeLog_worksCorrectly(): void
    {
        $changeLog = '1. 新增3个安全案例\n2. 优化视频画质\n3. 修复已知问题';
        $result = $this->version->setChangeLog($changeLog);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($changeLog, $this->version->getChangeLog());
    }

    public function test_getChangeLog_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getChangeLog());
    }

    public function test_setAndGetStatus_worksCorrectly(): void
    {
        $status = 'published';
        $result = $this->version->setStatus($status);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($status, $this->version->getStatus());
    }

    public function test_getStatus_hasDefaultValue(): void
    {
        $this->assertSame('draft', $this->version->getStatus());
    }

    public function test_setAndGetIsCurrent_worksCorrectly(): void
    {
        $result = $this->version->setIsCurrent(true);
        
        $this->assertSame($this->version, $result);
        $this->assertTrue($this->version->isIsCurrent());
        
        $this->version->setIsCurrent(false);
        $this->assertFalse($this->version->isIsCurrent());
    }

    public function test_isIsCurrent_hasDefaultValue(): void
    {
        $this->assertFalse($this->version->isIsCurrent());
    }

    public function test_setAndGetCourseSnapshot_worksCorrectly(): void
    {
        $snapshot = ['id' => '123', 'title' => '安全培训', 'price' => 100];
        $result = $this->version->setCourseSnapshot($snapshot);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($snapshot, $this->version->getCourseSnapshot());
    }

    public function test_getCourseSnapshot_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getCourseSnapshot());
    }

    public function test_setAndGetChaptersSnapshot_worksCorrectly(): void
    {
        $snapshot = [
            ['id' => '1', 'title' => '第一章', 'sort_number' => 1],
            ['id' => '2', 'title' => '第二章', 'sort_number' => 2]
        ];
        $result = $this->version->setChaptersSnapshot($snapshot);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($snapshot, $this->version->getChaptersSnapshot());
    }

    public function test_getChaptersSnapshot_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getChaptersSnapshot());
    }

    public function test_setAndGetLessonsSnapshot_worksCorrectly(): void
    {
        $snapshot = [
            ['id' => '1', 'title' => '课时1', 'duration' => 1800],
            ['id' => '2', 'title' => '课时2', 'duration' => 2400]
        ];
        $result = $this->version->setLessonsSnapshot($snapshot);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($snapshot, $this->version->getLessonsSnapshot());
    }

    public function test_getLessonsSnapshot_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getLessonsSnapshot());
    }

    public function test_setAndGetPublishedAt_worksCorrectly(): void
    {
        $publishedAt = new \DateTime('2023-12-01 15:30:00');
        $result = $this->version->setPublishedAt($publishedAt);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($publishedAt, $this->version->getPublishedAt());
    }

    public function test_getPublishedAt_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getPublishedAt());
    }

    public function test_setAndGetPublishedBy_worksCorrectly(): void
    {
        $publishedBy = 'admin123';
        $result = $this->version->setPublishedBy($publishedBy);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($publishedBy, $this->version->getPublishedBy());
    }

    public function test_getPublishedBy_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getPublishedBy());
    }

    public function test_setAndGetMetadata_worksCorrectly(): void
    {
        $metadata = ['build' => '20231201', 'environment' => 'production'];
        $result = $this->version->setMetadata($metadata);
        
        $this->assertSame($this->version, $result);
        $this->assertSame($metadata, $this->version->getMetadata());
    }

    public function test_getMetadata_returnsNullByDefault(): void
    {
        $this->assertNull($this->version->getMetadata());
    }

    public function test_isPublished_withPublishedStatus_returnsTrue(): void
    {
        $this->version->setStatus('published');
        $this->assertTrue($this->version->isPublished());
    }

    public function test_isPublished_withOtherStatus_returnsFalse(): void
    {
        $this->version->setStatus('draft');
        $this->assertFalse($this->version->isPublished());
        
        $this->version->setStatus('archived');
        $this->assertFalse($this->version->isPublished());
    }

    public function test_isDraft_withDraftStatus_returnsTrue(): void
    {
        $this->version->setStatus('draft');
        $this->assertTrue($this->version->isDraft());
    }

    public function test_isDraft_withOtherStatus_returnsFalse(): void
    {
        $this->version->setStatus('published');
        $this->assertFalse($this->version->isDraft());
        
        $this->version->setStatus('archived');
        $this->assertFalse($this->version->isDraft());
    }

    public function test_getStatusLabel_returnsCorrectLabels(): void
    {
        $this->version->setStatus('draft');
        $this->assertSame('草稿', $this->version->getStatusLabel());
        
        $this->version->setStatus('published');
        $this->assertSame('已发布', $this->version->getStatusLabel());
        
        $this->version->setStatus('archived');
        $this->assertSame('已归档', $this->version->getStatusLabel());
        
        $this->version->setStatus('deprecated');
        $this->assertSame('已废弃', $this->version->getStatusLabel());
        
        $this->version->setStatus('unknown');
        $this->assertSame('未知状态', $this->version->getStatusLabel());
    }

    public function test_defaultValues_areSetCorrectly(): void
    {
        $this->assertSame('draft', $this->version->getStatus());
        $this->assertFalse($this->version->isIsCurrent());
        $this->assertNull($this->version->getCourse());
        $this->assertNull($this->version->getVersion());
        $this->assertNull($this->version->getTitle());
        $this->assertNull($this->version->getDescription());
        $this->assertNull($this->version->getChangeLog());
        $this->assertNull($this->version->getCourseSnapshot());
        $this->assertNull($this->version->getChaptersSnapshot());
        $this->assertNull($this->version->getLessonsSnapshot());
        $this->assertNull($this->version->getPublishedAt());
        $this->assertNull($this->version->getPublishedBy());
        $this->assertNull($this->version->getMetadata());
    }

    public function test_versionWorkflow_worksCorrectly(): void
    {
        // 初始状态
        $this->assertTrue($this->version->isDraft());
        $this->assertFalse($this->version->isPublished());
        $this->assertFalse($this->version->isIsCurrent());
        
        // 设置版本信息
        $this->version->setVersion('v1.0.0');
        $this->version->setTitle('初始版本');
        $this->version->setChangeLog('首次发布');
        
        // 发布版本
        $this->version->setStatus('published');
        $this->version->setPublishedAt(new \DateTime());
        $this->version->setPublishedBy('admin');
        $this->version->setIsCurrent(true);
        
        $this->assertTrue($this->version->isPublished());
        $this->assertFalse($this->version->isDraft());
        $this->assertTrue($this->version->isIsCurrent());
        
        // 归档版本
        $this->version->setStatus('archived');
        $this->version->setIsCurrent(false);
        
        $this->assertFalse($this->version->isPublished());
        $this->assertFalse($this->version->isDraft());
        $this->assertFalse($this->version->isIsCurrent());
    }

    public function test_complexSnapshots_workCorrectly(): void
    {
        $courseSnapshot = [
            'id' => '123456789',
            'title' => '安全生产培训课程',
            'description' => '全面的安全生产知识培训',
            'price' => 299.99,
            'valid_day' => 365,
            'learn_hour' => 40,
            'teacher_name' => '张教授',
            'category' => 'safety'
        ];
        
        $chaptersSnapshot = [
            [
                'id' => '1',
                'title' => '安全生产法律法规',
                'sort_number' => 1,
                'lesson_count' => 5
            ],
            [
                'id' => '2', 
                'title' => '安全管理制度',
                'sort_number' => 2,
                'lesson_count' => 8
            ]
        ];
        
        $lessonsSnapshot = [
            [
                'id' => '1',
                'chapter_id' => '1',
                'title' => '安全生产法概述',
                'duration_second' => 1800,
                'sort_number' => 1
            ],
            [
                'id' => '2',
                'chapter_id' => '1', 
                'title' => '安全生产责任制',
                'duration_second' => 2400,
                'sort_number' => 2
            ]
        ];
        
        $this->version->setCourseSnapshot($courseSnapshot);
        $this->version->setChaptersSnapshot($chaptersSnapshot);
        $this->version->setLessonsSnapshot($lessonsSnapshot);
        
        $this->assertSame($courseSnapshot, $this->version->getCourseSnapshot());
        $this->assertSame($chaptersSnapshot, $this->version->getChaptersSnapshot());
        $this->assertSame($lessonsSnapshot, $this->version->getLessonsSnapshot());
        
        // 验证快照数据的完整性
        $this->assertSame('安全生产培训课程', $this->version->getCourseSnapshot()['title']);
        $this->assertCount(2, $this->version->getChaptersSnapshot());
        $this->assertCount(2, $this->version->getLessonsSnapshot());
        $this->assertSame(5, $this->version->getChaptersSnapshot()[0]['lesson_count']);
        $this->assertSame(1800, $this->version->getLessonsSnapshot()[0]['duration_second']);
    }
} 