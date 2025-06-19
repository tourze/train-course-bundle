<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\TrainCourseBundle\Repository\CourseAuditRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程审核实体
 * 
 * 管理课程的审核流程，包括审核状态、审核意见、审核人员等信息
 * 支持多级审核和审核历史记录
 */
#[ORM\Entity(repositoryClass: CourseAuditRepository::class)]
#[ORM\Table(name: 'train_course_audit', options: ['comment' => '课程审核'])]
class CourseAudit implements Stringable
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

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    private string $status = 'pending';

    private string $auditType = 'content';

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '审核人员'])]
    private ?string $auditor = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '审核意见'])]
    private ?string $auditComment = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审核时间'])]
    private ?\DateTimeInterface $auditTime = null;

    private int $auditLevel = 1;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '优先级', 'default' => 0])]
    private int $priority = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '截止时间'])]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '审核数据'])]
    private ?array $auditData = null;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getAuditType(): string
    {
        return $this->auditType;
    }

    public function setAuditType(string $auditType): static
    {
        $this->auditType = $auditType;
        return $this;
    }

    public function getAuditor(): ?string
    {
        return $this->auditor;
    }

    public function setAuditor(?string $auditor): static
    {
        $this->auditor = $auditor;
        return $this;
    }

    public function getAuditComment(): ?string
    {
        return $this->auditComment;
    }

    public function setAuditComment(?string $auditComment): static
    {
        $this->auditComment = $auditComment;
        return $this;
    }

    public function getAuditTime(): ?\DateTimeInterface
    {
        return $this->auditTime;
    }

    public function setAuditTime(?\DateTimeInterface $auditTime): static
    {
        $this->auditTime = $auditTime;
        return $this;
    }

    public function getAuditLevel(): int
    {
        return $this->auditLevel;
    }

    public function setAuditLevel(int $auditLevel): static
    {
        $this->auditLevel = $auditLevel;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getAuditData(): ?array
    {
        return $this->auditData;
    }

    public function setAuditData(?array $auditData): static
    {
        $this->auditData = $auditData;
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
     * 检查审核是否通过
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * 检查审核是否被拒绝
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * 检查审核是否待处理
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 检查审核是否已超时
     */
    public function isOverdue(): bool
    {
        if (!$this->deadline) {
            return false;
        }
        
        return $this->deadline < new \DateTime() && $this->isPending();
    }

    /**
     * 获取审核状态的中文描述
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝',
            'in_progress' => '审核中',
            default => '未知状态',
        };
    }

    /**
     * 获取审核类型的中文描述
     */
    public function getAuditTypeLabel(): string
    {
        return match ($this->auditType) {
            'content' => '内容审核',
            'quality' => '质量审核',
            'compliance' => '合规审核',
            'final' => '终审',
            default => '其他审核',
        };
    }

    /**
     * 获取提交时间（使用创建时间作为提交时间）
     */
    public function getSubmitTime(): ?\DateTimeInterface
    {
        return $this->getCreateTime();
    }

    /**
     * 设置审核人ID（兼容旧方法名）
     */
    public function setAuditorId(?string $auditorId): static
    {
        $this->auditor = $auditorId;
        return $this;
    }

    /**
     * 设置拒绝原因（使用审核意见字段）
     */
    public function setRejectReason(?string $reason): static
    {
        $this->auditComment = $reason;
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 