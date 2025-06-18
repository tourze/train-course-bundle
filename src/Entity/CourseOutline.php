<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程大纲实体
 * 
 * 存储课程的详细大纲信息，包括学习目标、内容要点、考核标准等
 * 支持结构化的课程内容组织和管理
 */
#[ORM\Entity(repositoryClass: CourseOutlineRepository::class)]
#[ORM\Table(name: 'train_course_outline', options: ['comment' => '课程大纲'])]
class CourseOutline implements Stringable
{
    use TimestampableTrait;

    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[CreatedByColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'outlines')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '学习目标'])]
    private ?string $learningObjectives = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '内容要点'])]
    private ?string $contentPoints = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '重点难点'])]
    private ?string $keyDifficulties = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '考核标准'])]
    private ?string $assessmentCriteria = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '参考资料'])]
    private ?string $references = null;

    private ?int $estimatedMinutes = null;

    private int $sortNumber = 0;

    private string $status = 'draft';

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    private ?array $metadata = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getLearningObjectives(): ?string
    {
        return $this->learningObjectives;
    }

    public function setLearningObjectives(?string $learningObjectives): static
    {
        $this->learningObjectives = $learningObjectives;
        return $this;
    }

    public function getContentPoints(): ?string
    {
        return $this->contentPoints;
    }

    public function setContentPoints(?string $contentPoints): static
    {
        $this->contentPoints = $contentPoints;
        return $this;
    }

    public function getKeyDifficulties(): ?string
    {
        return $this->keyDifficulties;
    }

    public function setKeyDifficulties(?string $keyDifficulties): static
    {
        $this->keyDifficulties = $keyDifficulties;
        return $this;
    }

    public function getAssessmentCriteria(): ?string
    {
        return $this->assessmentCriteria;
    }

    public function setAssessmentCriteria(?string $assessmentCriteria): static
    {
        $this->assessmentCriteria = $assessmentCriteria;
        return $this;
    }

    public function getReferences(): ?string
    {
        return $this->references;
    }

    public function setReferences(?string $references): static
    {
        $this->references = $references;
        return $this;
    }

    public function getEstimatedMinutes(): ?int
    {
        return $this->estimatedMinutes;
    }

    public function setEstimatedMinutes(?int $estimatedMinutes): static
    {
        $this->estimatedMinutes = $estimatedMinutes;
        return $this;
    }

    public function getSortNumber(): int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(int $sortNumber): static
    {
        $this->sortNumber = $sortNumber;
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
     * 检查大纲是否已发布
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * 获取预计学时（小时）
     */
    public function getEstimatedHours(): float
    {
        return $this->estimatedMinutes ? round($this->estimatedMinutes / 60, 2) : 0;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 