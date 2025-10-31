<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\CollectRepository;

/**
 * 课程收藏实体
 *
 * 管理用户对课程的收藏功能，支持收藏分组和备注
 * 一个用户对同一课程只能收藏一次
 */
#[ORM\Entity(repositoryClass: CollectRepository::class)]
#[ORM\Table(name: 'train_course_collect', options: ['comment' => '课程收藏'])]
#[ORM\UniqueConstraint(name: 'unique_user_course', columns: ['user_id', 'course_id'])]
class Collect implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Column(type: Types::STRING, nullable: false, options: ['comment' => '用户ID'])]
    #[Assert\NotBlank(message: '用户ID不能为空')]
    #[Assert\Length(max: 50, maxMessage: '用户ID长度不能超过 {{ limit }} 个字符')]
    private ?string $userId = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '收藏状态', 'default' => 'active'])]
    #[Assert\NotBlank(message: '收藏状态不能为空')]
    #[Assert\Length(max: 20, maxMessage: '收藏状态长度不能超过 {{ limit }} 个字符')]
    #[Assert\Choice(choices: ['active', 'cancelled', 'hidden'], message: '收藏状态必须是: {{ choices }}')]
    private string $status = 'active';

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '收藏分组'])]
    #[Assert\Length(max: 100, maxMessage: '收藏分组名称长度不能超过 {{ limit }} 个字符')]
    private ?string $collectGroup = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '收藏备注'])]
    #[Assert\Length(max: 65535, maxMessage: '收藏备注长度不能超过 {{ limit }} 个字符')]
    private ?string $note = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '排序号', 'default' => 0])]
    #[Assert\PositiveOrZero(message: '排序号必须大于或等于0')]
    private int $sortNumber = 0;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否置顶', 'default' => false])]
    #[Assert\Type(type: 'bool', message: '置顶状态必须是布尔值')]
    private bool $isTop = false;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    #[Assert\Type(type: 'array', message: '扩展属性必须是数组类型')]
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCollectGroup(): ?string
    {
        return $this->collectGroup;
    }

    public function setCollectGroup(?string $collectGroup): void
    {
        $this->collectGroup = $collectGroup;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function getSortNumber(): int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(int $sortNumber): void
    {
        $this->sortNumber = $sortNumber;
    }

    public function isIsTop(): bool
    {
        return $this->isTop;
    }

    public function setIsTop(bool $isTop): void
    {
        $this->isTop = $isTop;
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
     * 检查收藏是否有效
     */
    public function isActive(): bool
    {
        return 'active' === $this->status;
    }

    /**
     * 获取收藏状态的中文描述
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => '已收藏',
            'cancelled' => '已取消',
            'hidden' => '已隐藏',
            default => '未知状态',
        };
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
