<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\CourseVersionRepository;

/**
 * 课程版本实体
 *
 * 管理课程的版本控制，记录课程的历史变更和版本信息
 * 支持版本回滚和变更追踪
 */
#[ORM\Entity(repositoryClass: CourseVersionRepository::class)]
#[ORM\Table(name: 'train_course_version', options: ['comment' => '课程版本'])]
class CourseVersion implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '版本号'])]
    #[Assert\Length(max: 50)]
    private ?string $version = null;

    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '版本标题'])]
    #[Assert\Length(max: 200)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '版本描述'])]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '变更说明'])]
    #[Assert\Length(max: 65535)]
    private ?string $changeLog = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '版本状态', 'default' => 'draft'])]
    #[Assert\Length(max: 20)]
    #[Assert\Choice(choices: ['draft', 'published', 'archived', 'deprecated'])]
    private string $status = 'draft';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否为当前版本', 'default' => false])]
    #[Assert\NotNull]
    private bool $isCurrent = false;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '课程数据快照'])]
    #[Assert\Type(type: 'array')]
    private ?array $courseSnapshot = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '章节数据快照'])]
    #[Assert\Type(type: 'array')]
    private ?array $chaptersSnapshot = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '课时数据快照'])]
    #[Assert\Type(type: 'array')]
    private ?array $lessonsSnapshot = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '发布时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '发布人'])]
    #[Assert\Length(max: 100)]
    private ?string $publishedBy = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    #[Assert\Type(type: 'array')]
    private ?array $metadata = null;

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getChangeLog(): ?string
    {
        return $this->changeLog;
    }

    public function setChangeLog(?string $changeLog): void
    {
        $this->changeLog = $changeLog;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isIsCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): void
    {
        $this->isCurrent = $isCurrent;
    }

    /** @return array<string, mixed>|null */
    public function getCourseSnapshot(): ?array
    {
        return $this->courseSnapshot;
    }

    /** @param array<string, mixed>|null $courseSnapshot */
    public function setCourseSnapshot(?array $courseSnapshot): void
    {
        $this->courseSnapshot = $courseSnapshot;
    }

    /** @return array<int, array<string, mixed>>|null */
    public function getChaptersSnapshot(): ?array
    {
        return $this->chaptersSnapshot;
    }

    /** @param array<int, array<string, mixed>>|null $chaptersSnapshot */
    public function setChaptersSnapshot(?array $chaptersSnapshot): void
    {
        $this->chaptersSnapshot = $chaptersSnapshot;
    }

    /** @return array<int, array<string, mixed>>|null */
    public function getLessonsSnapshot(): ?array
    {
        return $this->lessonsSnapshot;
    }

    /** @param array<int, array<string, mixed>>|null $lessonsSnapshot */
    public function setLessonsSnapshot(?array $lessonsSnapshot): void
    {
        $this->lessonsSnapshot = $lessonsSnapshot;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getPublishedBy(): ?string
    {
        return $this->publishedBy;
    }

    public function setPublishedBy(?string $publishedBy): void
    {
        $this->publishedBy = $publishedBy;
    }

    /** @return array<string, mixed>|null */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /** @param array<string, mixed>|null $metadata */
    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * 检查版本是否已发布
     */
    public function isPublished(): bool
    {
        return 'published' === $this->status;
    }

    /**
     * 检查版本是否为草稿
     */
    public function isDraft(): bool
    {
        return 'draft' === $this->status;
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
    /** @return array<string, mixed> */
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
