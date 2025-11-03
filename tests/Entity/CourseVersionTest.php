<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseVersion;

/**
 * CourseVersion 实体单元测试
 *
 * @internal
 */
#[CoversClass(CourseVersion::class)]
final class CourseVersionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CourseVersion();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'status' => ['status', 'test_value'],
            'isCurrent' => ['isCurrent', true],
        ];
    }

    private CourseVersion $version;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->version = new CourseVersion();
    }

    public function testGetIdReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getId());
    }

    public function testSetAndGetCreatedByWorksCorrectly(): void
    {
        $createdBy = 'user123';
        $this->version->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $this->version->getCreatedBy());
    }

    public function testSetAndGetUpdatedByWorksCorrectly(): void
    {
        $updatedBy = 'user456';
        $this->version->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $this->version->getUpdatedBy());
    }

    public function testSetAndGetCourseWorksCorrectly(): void
    {
        $course = new Course();
        $this->version->setCourse($course);

        $this->assertSame($course, $this->version->getCourse());
    }

    public function testGetCourseReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getCourse());
    }

    public function testSetAndGetVersionWorksCorrectly(): void
    {
        $versionNumber = 'v2.1.0';
        $this->version->setVersion($versionNumber);

        $this->assertSame($versionNumber, $this->version->getVersion());
    }

    public function testGetVersionReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getVersion());
    }

    public function testSetAndGetTitleWorksCorrectly(): void
    {
        $title = '安全生产培训课程 v2.1';
        $this->version->setTitle($title);

        $this->assertSame($title, $this->version->getTitle());
    }

    public function testGetTitleReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getTitle());
    }

    public function testSetAndGetDescriptionWorksCorrectly(): void
    {
        $description = '本版本增加了新的安全案例分析和互动练习';
        $this->version->setDescription($description);

        $this->assertSame($description, $this->version->getDescription());
    }

    public function testGetDescriptionReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getDescription());
    }

    public function testSetAndGetChangeLogWorksCorrectly(): void
    {
        $changeLog = '1. 新增3个安全案例\n2. 优化视频画质\n3. 修复已知问题';
        $this->version->setChangeLog($changeLog);

        $this->assertSame($changeLog, $this->version->getChangeLog());
    }

    public function testGetChangeLogReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getChangeLog());
    }

    public function testSetAndGetStatusWorksCorrectly(): void
    {
        $status = 'published';
        $this->version->setStatus($status);

        $this->assertSame($status, $this->version->getStatus());
    }

    public function testGetStatusHasDefaultValue(): void
    {
        $this->assertSame('draft', $this->version->getStatus());
    }

    public function testSetAndGetIsCurrentWorksCorrectly(): void
    {
        $this->version->setIsCurrent(true);

        $this->assertTrue($this->version->isIsCurrent());

        $this->version->setIsCurrent(false);
        $this->assertFalse($this->version->isIsCurrent());
    }

    public function testIsIsCurrentHasDefaultValue(): void
    {
        $this->assertFalse($this->version->isIsCurrent());
    }

    public function testSetAndGetCourseSnapshotWorksCorrectly(): void
    {
        $snapshot = ['id' => '123', 'title' => '安全培训', 'price' => 100];
        $this->version->setCourseSnapshot($snapshot);

        $this->assertSame($snapshot, $this->version->getCourseSnapshot());
    }

    public function testGetCourseSnapshotReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getCourseSnapshot());
    }

    public function testSetAndGetChaptersSnapshotWorksCorrectly(): void
    {
        $snapshot = [
            ['id' => '1', 'title' => '第一章', 'sort_number' => 1],
            ['id' => '2', 'title' => '第二章', 'sort_number' => 2],
        ];
        $this->version->setChaptersSnapshot($snapshot);

        $this->assertSame($snapshot, $this->version->getChaptersSnapshot());
    }

    public function testGetChaptersSnapshotReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getChaptersSnapshot());
    }

    public function testSetAndGetLessonsSnapshotWorksCorrectly(): void
    {
        $snapshot = [
            ['id' => '1', 'title' => '课时1', 'duration' => 1800],
            ['id' => '2', 'title' => '课时2', 'duration' => 2400],
        ];
        $this->version->setLessonsSnapshot($snapshot);

        $this->assertSame($snapshot, $this->version->getLessonsSnapshot());
    }

    public function testGetLessonsSnapshotReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getLessonsSnapshot());
    }

    public function testSetAndGetPublishedAtWorksCorrectly(): void
    {
        $publishedAt = new \DateTimeImmutable('2023-12-01 15:30:00');
        $this->version->setPublishedAt($publishedAt);

        $this->assertSame($publishedAt, $this->version->getPublishedAt());
    }

    public function testGetPublishedAtReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getPublishedAt());
    }

    public function testSetAndGetPublishedByWorksCorrectly(): void
    {
        $publishedBy = 'admin123';
        $this->version->setPublishedBy($publishedBy);

        $this->assertSame($publishedBy, $this->version->getPublishedBy());
    }

    public function testGetPublishedByReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getPublishedBy());
    }

    public function testSetAndGetMetadataWorksCorrectly(): void
    {
        $metadata = ['build' => '20231201', 'environment' => 'production'];
        $this->version->setMetadata($metadata);

        $this->assertSame($metadata, $this->version->getMetadata());
    }

    public function testGetMetadataReturnsNullByDefault(): void
    {
        $this->assertNull($this->version->getMetadata());
    }

    public function testIsPublishedWithPublishedStatusReturnsTrue(): void
    {
        $this->version->setStatus('published');
        $this->assertTrue($this->version->isPublished());
    }

    public function testIsPublishedWithOtherStatusReturnsFalse(): void
    {
        $this->version->setStatus('draft');
        $this->assertFalse($this->version->isPublished());

        $this->version->setStatus('archived');
        $this->assertFalse($this->version->isPublished());
    }

    public function testIsDraftWithDraftStatusReturnsTrue(): void
    {
        $this->version->setStatus('draft');
        $this->assertTrue($this->version->isDraft());
    }

    public function testIsDraftWithOtherStatusReturnsFalse(): void
    {
        $this->version->setStatus('published');
        $this->assertFalse($this->version->isDraft());

        $this->version->setStatus('archived');
        $this->assertFalse($this->version->isDraft());
    }

    public function testGetStatusLabelReturnsCorrectLabels(): void
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

    public function testDefaultValuesAreSetCorrectly(): void
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

    public function testVersionWorkflowWorksCorrectly(): void
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
        $this->version->setPublishedAt(new \DateTimeImmutable());
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

    public function testComplexSnapshotsWorkCorrectly(): void
    {
        $courseSnapshot = [
            'id' => '123456789',
            'title' => '安全生产培训课程',
            'description' => '全面的安全生产知识培训',
            'price' => 299.99,
            'valid_day' => 365,
            'learn_hour' => 40,
            'teacher_name' => '张教授',
            'category' => 'safety',
        ];

        $chaptersSnapshot = [
            [
                'id' => '1',
                'title' => '安全生产法律法规',
                'sort_number' => 1,
                'lesson_count' => 5,
            ],
            [
                'id' => '2',
                'title' => '安全管理制度',
                'sort_number' => 2,
                'lesson_count' => 8,
            ],
        ];

        $lessonsSnapshot = [
            [
                'id' => '1',
                'chapter_id' => '1',
                'title' => '安全生产法概述',
                'duration_second' => 1800,
                'sort_number' => 1,
            ],
            [
                'id' => '2',
                'chapter_id' => '1',
                'title' => '安全生产责任制',
                'duration_second' => 2400,
                'sort_number' => 2,
            ],
        ];

        $this->version->setCourseSnapshot($courseSnapshot);
        $this->version->setChaptersSnapshot($chaptersSnapshot);
        $this->version->setLessonsSnapshot($lessonsSnapshot);

        $this->assertSame($courseSnapshot, $this->version->getCourseSnapshot());
        $this->assertSame($chaptersSnapshot, $this->version->getChaptersSnapshot());
        $this->assertSame($lessonsSnapshot, $this->version->getLessonsSnapshot());

        // 验证快照数据的完整性
        $courseSnapshot = $this->version->getCourseSnapshot();
        $chaptersSnapshot = $this->version->getChaptersSnapshot();
        $lessonsSnapshot = $this->version->getLessonsSnapshot();

        $this->assertNotNull($courseSnapshot);
        $this->assertNotNull($chaptersSnapshot);

        $this->assertSame('安全生产培训课程', $courseSnapshot['title']);
        $this->assertCount(2, $chaptersSnapshot);
        $this->assertCount(2, $lessonsSnapshot);
        $this->assertSame(5, $chaptersSnapshot[0]['lesson_count']);
        $this->assertSame(1800, $lessonsSnapshot[0]['duration_second']);
    }
}
