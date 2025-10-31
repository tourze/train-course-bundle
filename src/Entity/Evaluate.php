<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\EvaluateRepository;

/**
 * 课程评价实体
 *
 * 管理用户对课程的评价和评分功能，支持星级评分、文字评价、审核等
 * 一个用户对同一课程只能评价一次
 */
#[ORM\Entity(repositoryClass: EvaluateRepository::class)]
#[ORM\Table(name: 'train_course_evaluate', options: ['comment' => '课程评价'])]
#[ORM\UniqueConstraint(name: 'unique_user_course_evaluate', columns: ['user_id', 'course_id'])]
class Evaluate implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: false, options: ['comment' => '用户ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $userId = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '评分(1-5星)', 'default' => 5])]
    #[Assert\Range(min: 1, max: 5)]
    private int $rating = 5;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '评价内容'])]
    #[Assert\Length(max: 65535)]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '评价状态', 'default' => 'published'])]
    #[Assert\Length(max: 20)]
    #[Assert\Choice(choices: ['published', 'pending', 'rejected', 'hidden'])]
    private string $status = 'published';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否匿名', 'default' => false])]
    #[Assert\NotNull]
    private bool $isAnonymous = false;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '点赞数', 'default' => 0])]
    #[Assert\PositiveOrZero]
    private int $likeCount = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '回复数', 'default' => 0])]
    #[Assert\PositiveOrZero]
    private int $replyCount = 0;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '用户昵称'])]
    #[Assert\Length(max: 100)]
    private ?string $userNickname = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '用户头像'])]
    #[Assert\Length(max: 255)]
    private ?string $userAvatar = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审核时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $auditTime = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '审核人员'])]
    #[Assert\Length(max: 100)]
    private ?string $auditor = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '审核意见'])]
    #[Assert\Length(max: 65535)]
    private ?string $auditComment = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    #[Assert\Type(type: 'array')]
    private ?array $metadata = null;

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        // 确保评分在1-5之间
        $this->rating = max(1, min(5, $rating));
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isIsAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->isAnonymous = $isAnonymous;
    }

    public function getLikeCount(): int
    {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): void
    {
        $this->likeCount = max(0, $likeCount);
    }

    public function getReplyCount(): int
    {
        return $this->replyCount;
    }

    public function setReplyCount(int $replyCount): void
    {
        $this->replyCount = max(0, $replyCount);
    }

    public function getUserNickname(): ?string
    {
        return $this->userNickname;
    }

    public function setUserNickname(?string $userNickname): void
    {
        $this->userNickname = $userNickname;
    }

    public function getUserAvatar(): ?string
    {
        return $this->userAvatar;
    }

    public function setUserAvatar(?string $userAvatar): void
    {
        $this->userAvatar = $userAvatar;
    }

    public function getAuditTime(): ?\DateTimeInterface
    {
        return $this->auditTime;
    }

    public function setAuditTime(?\DateTimeInterface $auditTime): void
    {
        $this->auditTime = $auditTime;
    }

    public function getAuditor(): ?string
    {
        return $this->auditor;
    }

    public function setAuditor(?string $auditor): void
    {
        $this->auditor = $auditor;
    }

    public function getAuditComment(): ?string
    {
        return $this->auditComment;
    }

    public function setAuditComment(?string $auditComment): void
    {
        $this->auditComment = $auditComment;
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
     * 检查评价是否已发布
     */
    public function isPublished(): bool
    {
        return 'published' === $this->status;
    }

    /**
     * 检查评价是否待审核
     */
    public function isPending(): bool
    {
        return 'pending' === $this->status;
    }

    /**
     * 检查评价是否被拒绝
     */
    public function isRejected(): bool
    {
        return 'rejected' === $this->status;
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
    public function incrementLikeCount(): void
    {
        ++$this->likeCount;
    }

    /**
     * 减少点赞数
     */
    public function decrementLikeCount(): void
    {
        $this->likeCount = max(0, $this->likeCount - 1);
    }

    /**
     * 增加回复数
     */
    public function incrementReplyCount(): void
    {
        ++$this->replyCount;
    }

    /**
     * 减少回复数
     */
    public function decrementReplyCount(): void
    {
        $this->replyCount = max(0, $this->replyCount - 1);
    }

    /**
     * 获取显示的用户名称
     */
    public function getDisplayUserName(): string
    {
        if ($this->isAnonymous) {
            return '匿名用户';
        }

        return $this->userNickname ?? '用户' . substr($this->userId ?? '', -4);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
