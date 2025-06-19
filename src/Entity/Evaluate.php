<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程评价实体
 * 
 * 管理用户对课程的评价和评分功能，支持星级评分、文字评价、审核等
 * 一个用户对同一课程只能评价一次
 */
#[ORM\Entity(repositoryClass: EvaluateRepository::class)]
#[ORM\Table(name: 'train_course_evaluate', options: ['comment' => '课程评价'])]
#[ORM\UniqueConstraint(name: 'unique_user_course_evaluate', columns: ['user_id', 'course_id'])]
class Evaluate implements Stringable
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

    #[ORM\Column(type: Types::STRING, nullable: false, options: ['comment' => '用户ID'])]
    private ?string $userId = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '评分(1-5星)', 'default' => 5])]
    private int $rating = 5;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '评价内容'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '评价状态', 'default' => 'published'])]
    private string $status = 'published';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否匿名', 'default' => false])]
    private bool $isAnonymous = false;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '点赞数', 'default' => 0])]
    private int $likeCount = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '回复数', 'default' => 0])]
    private int $replyCount = 0;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '用户昵称'])]
    private ?string $userNickname = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '用户头像'])]
    private ?string $userAvatar = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审核时间'])]
    private ?\DateTimeInterface $auditTime = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '审核人员'])]
    private ?string $auditor = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '审核意见'])]
    private ?string $auditComment = null;

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

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;
        return $this;
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

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        // 确保评分在1-5之间
        $this->rating = max(1, min(5, $rating));
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
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

    public function isIsAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): static
    {
        $this->isAnonymous = $isAnonymous;
        return $this;
    }

    public function getLikeCount(): int
    {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): static
    {
        $this->likeCount = max(0, $likeCount);
        return $this;
    }

    public function getReplyCount(): int
    {
        return $this->replyCount;
    }

    public function setReplyCount(int $replyCount): static
    {
        $this->replyCount = max(0, $replyCount);
        return $this;
    }

    public function getUserNickname(): ?string
    {
        return $this->userNickname;
    }

    public function setUserNickname(?string $userNickname): static
    {
        $this->userNickname = $userNickname;
        return $this;
    }

    public function getUserAvatar(): ?string
    {
        return $this->userAvatar;
    }

    public function setUserAvatar(?string $userAvatar): static
    {
        $this->userAvatar = $userAvatar;
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
     * 检查评价是否已发布
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * 检查评价是否待审核
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 检查评价是否被拒绝
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * 获取评价状态的中文描述
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'published' => '已发布',
            'pending' => '待审核',
            'rejected' => '已拒绝',
            'hidden' => '已隐藏',
            default => '未知状态',
        };
    }

    /**
     * 获取星级评分的文字描述
     */
    public function getRatingLabel(): string
    {
        return match ($this->rating) {
            1 => '很差',
            2 => '较差',
            3 => '一般',
            4 => '较好',
            5 => '很好',
            default => '未知',
        };
    }

    /**
     * 增加点赞数
     */
    public function incrementLikeCount(): static
    {
        $this->likeCount++;
        return $this;
    }

    /**
     * 减少点赞数
     */
    public function decrementLikeCount(): static
    {
        $this->likeCount = max(0, $this->likeCount - 1);
        return $this;
    }

    /**
     * 增加回复数
     */
    public function incrementReplyCount(): static
    {
        $this->replyCount++;
        return $this;
    }

    /**
     * 减少回复数
     */
    public function decrementReplyCount(): static
    {
        $this->replyCount = max(0, $this->replyCount - 1);
        return $this;
    }

    /**
     * 获取显示的用户名称
     */
    public function getDisplayUserName(): string
    {
        if ($this->isAnonymous) {
            return '匿名用户';
        }
        
        return $this->userNickname ?: '用户' . substr($this->userId, -4);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 