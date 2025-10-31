<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\VideoRepository;

/**
 * 课程视频实体
 *
 * 存储课程相关的视频信息，关联阿里云VOD服务
 * 通过 aliyun-vod-bundle 管理视频的上传、转码、播放等功能
 */
#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: 'train_course_video', options: ['comment' => '课程视频'])]
class Video implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '视频标题'])]
    #[Assert\Length(max: 120, maxMessage: '视频标题长度不能超过 {{ limit }} 个字符')]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '阿里云视频ID'])]
    #[Assert\Length(max: 64, maxMessage: '视频ID长度不能超过 {{ limit }} 个字符')]
    private ?string $videoId = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '视频文件大小（字节）'])]
    #[Assert\PositiveOrZero(message: '视频文件大小必须大于或等于0')]
    #[Assert\Length(max: 20, maxMessage: '视频文件大小长度不能超过 {{ limit }} 个字符')]
    private ?string $size = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 3, nullable: true, options: ['comment' => '视频时长（秒）'])]
    #[Assert\PositiveOrZero(message: '视频时长必须大于或等于0')]
    #[Assert\Length(max: 25, maxMessage: '视频时长长度不能超过 {{ limit }} 个字符')]
    private ?string $duration = null;

    #[ORM\Column(length: 1000, nullable: true, options: ['comment' => '视频封面URL'])]
    #[Assert\Length(max: 1000, maxMessage: '视频封面URL长度不能超过 {{ limit }} 个字符')]
    #[Assert\Url(message: '视频封面必须是有效的URL地址')]
    private ?string $coverUrl = null;

    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '视频状态', 'default' => 'Normal'])]
    #[Assert\Length(max: 50, maxMessage: '视频状态长度不能超过 {{ limit }} 个字符')]
    #[Assert\Choice(choices: ['Normal', 'Processing', 'Failed', 'Deleted'], message: '视频状态必须是: {{ choices }}')]
    private ?string $status = 'Normal';

    #[ORM\ManyToOne(targetEntity: AliyunVodConfig::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL', options: ['comment' => '关联的阿里云VOD配置'])]
    private ?AliyunVodConfig $vodConfig = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): void
    {
        $this->videoId = $videoId;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): void
    {
        $this->size = $size;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): void
    {
        $this->duration = $duration;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): void
    {
        $this->coverUrl = $coverUrl;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getVodConfig(): ?AliyunVodConfig
    {
        return $this->vodConfig;
    }

    public function setVodConfig(?AliyunVodConfig $vodConfig): void
    {
        $this->vodConfig = $vodConfig;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
