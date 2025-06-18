<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\TrainCourseBundle\Repository\VideoRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程视频实体
 * 
 * 存储课程相关的视频信息，关联阿里云VOD服务
 * 通过 aliyun-vod-bundle 管理视频的上传、转码、播放等功能
 */
#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: 'train_course_video', options: ['comment' => '课程视频'])]
class Video implements Stringable
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

    private ?string $title = null;

    private ?string $videoId = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '视频文件大小（字节）'])]
    private ?string $size = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 3, nullable: true, options: ['comment' => '视频时长（秒）'])]
    private ?string $duration = null;

    #[ORM\Column(length: 1000, nullable: true, options: ['comment' => '视频封面URL'])]
    private ?string $coverUrl = null;

    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '视频状态', 'default' => 'Normal'])]
    private ?string $status = 'Normal';

    #[ORM\ManyToOne(targetEntity: AliyunVodConfig::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL', options: ['comment' => '关联的阿里云VOD配置'])]
    private ?AliyunVodConfig $vodConfig = null;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): static
    {
        $this->videoId = $videoId;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): static
    {
        $this->coverUrl = $coverUrl;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getVodConfig(): ?AliyunVodConfig
    {
        return $this->vodConfig;
    }

    public function setVodConfig(?AliyunVodConfig $vodConfig): static
    {
        $this->vodConfig = $vodConfig;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
