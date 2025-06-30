<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程版本实体
 *
 * 管理课程的版本控制，记录课程的历史变更和版本信息
 * 支持版本回滚和变更追踪
 */
#[ORM\Entity(repositoryClass: CourseVersionRepository::class)]
#[ORM\Table(name: 'train_course_version', options: ['comment' => '课程版本'])]
class CourseVersion implements Stringable
{
    use SnowflakeKeyAware;
    use TimestampableTrait;
    use BlameableAware;


    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    private ?string $version = null;

    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '版本描述'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '变更说明'])]
    private ?string $changeLog = null;

    private string $status = 'draft';

    private bool $isCurrent = false;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '课程数据快照'])]
    private ?array $courseSnapshot = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '章节数据快照'])]
    private ?array $chaptersSnapshot = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '课时数据快照'])]
    private ?array $lessonsSnapshot = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '发布时间'])]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '发布人'])]
    private ?string $publishedBy = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    private ?array $metadata = null;

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getChangeLog(): ?string
    {
        return $this->changeLog;
    }

    public function setChangeLog(?string $changeLog): static
    {
        $this->changeLog = $changeLog;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isIsCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;
        return $this;
    }

    public function getCourseSnapshot(): ?array
    {
        return $this->courseSnapshot;
    }

    public function setCourseSnapshot(?array $courseSnapshot): static
    {
        $this->courseSnapshot = $courseSnapshot;
        return $this;
    }

    public function getChaptersSnapshot(): ?array
    {
        return $this->chaptersSnapshot;
    }

    public function setChaptersSnapshot(?array $chaptersSnapshot): static
    {
        $this->chaptersSnapshot = $chaptersSnapshot;
        return $this;
    }

    public function getLessonsSnapshot(): ?array
    {
        return $this->lessonsSnapshot;
    }

    public function setLessonsSnapshot(?array $lessonsSnapshot): static
    {
        $this->lessonsSnapshot = $lessonsSnapshot;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getPublishedBy(): ?string
    {
        return $this->publishedBy;
    }

    public function setPublishedBy(?string $publishedBy): static
    {
        $this->publishedBy = $publishedBy;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * 检查版本是否已发布
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * 检查版本是否为草稿
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * 获取版本状态的中文描述
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => '草稿',
            'published' => '已发布',
            'archived' => '已归档',
            'deprecated' => '已废弃',
            default => '未知状态',
        };
    }

    /**
     * 获取完整的版本信息
     */
    public function getFullVersionInfo(): array
    {
        return [
            'id' => $this->getId(),
            'version' => $this->getVersion(),
            'title' => $this->getTitle(),
            'status' => $this->getStatus(),
            'status_label' => $this->getStatusLabel(),
            'is_current' => $this->isIsCurrent(),
            'published_at' => $this->getPublishedAt()?->format('Y-m-d H:i:s'),
            'published_by' => $this->getPublishedBy(),
            'created_at' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'created_by' => $this->getCreatedBy(),
        ];
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}