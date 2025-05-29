<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\TrainCourseBundle\Repository\CollectRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程收藏实体
 * 
 * 管理用户对课程的收藏功能，支持收藏分组和备注
 * 一个用户对同一课程只能收藏一次
 */
#[AsPermission(title: '课程收藏')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: CollectRepository::class)]
#[ORM\Table(name: 'train_course_collect', options: ['comment' => '课程收藏'])]
#[ORM\UniqueConstraint(name: 'unique_user_course', columns: ['user_id', 'course_id'])]
class Collect
{
    use TimestampableTrait;

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
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

    #[ListColumn]
    #[ORM\Column(length: 50, nullable: false, options: ['comment' => '用户ID'])]
    private ?string $userId = null;

    #[ListColumn]
    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    #[ListColumn]
    #[ORM\Column(length: 50, nullable: false, options: ['comment' => '收藏状态', 'default' => 'active'])]
    private string $status = 'active';

    #[ListColumn]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '收藏分组'])]
    private ?string $collectGroup = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '收藏备注'])]
    private ?string $note = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '排序号', 'default' => 0])]
    private int $sortNumber = 0;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否置顶', 'default' => false])]
    private bool $isTop = false;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCollectGroup(): ?string
    {
        return $this->collectGroup;
    }

    public function setCollectGroup(?string $collectGroup): static
    {
        $this->collectGroup = $collectGroup;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
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

    public function isIsTop(): bool
    {
        return $this->isTop;
    }

    public function setIsTop(bool $isTop): static
    {
        $this->isTop = $isTop;
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
     * 检查收藏是否有效
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
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
} 