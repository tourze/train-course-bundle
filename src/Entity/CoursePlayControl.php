<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\CoursePlayControlRepository;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;

/**
 * 课程播放控制实体
 * 
 * 管理课程的播放控制策略，包括防快进、倍速控制、水印设置等
 * 确保学习过程的完整性和合规性
 */
#[ORM\Entity(repositoryClass: CoursePlayControlRepository::class)]
#[ORM\Table(name: 'train_course_play_control', options: ['comment' => '课程播放控制'])]
class CoursePlayControl implements Stringable
{
    use TimestampableTrait;
    use BlameableAware;

    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    private bool $enabled = true;

    private bool $allowFastForward = false;

    private bool $allowSpeedControl = false;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '允许的播放倍速'])]
    private ?array $allowedSpeeds = null;

    private bool $enableWatermark = true;

    #[ORM\Column(length: 200, nullable: true, options: ['comment' => '水印文本'])]
    private ?string $watermarkText = null;

    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '水印位置'])]
    private ?string $watermarkPosition = 'bottom-right';

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '水印透明度（0-100）'])]
    private ?int $watermarkOpacity = 50;

    private int $maxDeviceCount = 3;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '播放凭证有效期（秒）', 'default' => 3600])]
    private int $playAuthDuration = 3600;

    private bool $enableResume = true;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '最小观看时长（秒）'])]
    private ?int $minWatchDuration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '观看进度检查间隔（秒）'])]
    private ?int $progressCheckInterval = 30;

    private bool $allowSeeking = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用右键菜单', 'default' => false])]
    private bool $allowContextMenu = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否允许下载', 'default' => false])]
    private bool $allowDownload = false;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展配置'])]
    private ?array $extendedConfig = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    private ?array $metadata = null;

    public function getId(): ?string
    {
        return $this->id;
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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isAllowFastForward(): bool
    {
        return $this->allowFastForward;
    }

    public function setAllowFastForward(bool $allowFastForward): static
    {
        $this->allowFastForward = $allowFastForward;
        return $this;
    }

    public function isAllowSpeedControl(): bool
    {
        return $this->allowSpeedControl;
    }

    public function setAllowSpeedControl(bool $allowSpeedControl): static
    {
        $this->allowSpeedControl = $allowSpeedControl;
        return $this;
    }

    public function getAllowedSpeeds(): ?array
    {
        return $this->allowedSpeeds;
    }

    public function setAllowedSpeeds(?array $allowedSpeeds): static
    {
        $this->allowedSpeeds = $allowedSpeeds;
        return $this;
    }

    public function isEnableWatermark(): bool
    {
        return $this->enableWatermark;
    }

    public function setEnableWatermark(bool $enableWatermark): static
    {
        $this->enableWatermark = $enableWatermark;
        return $this;
    }

    public function getWatermarkText(): ?string
    {
        return $this->watermarkText;
    }

    public function setWatermarkText(?string $watermarkText): static
    {
        $this->watermarkText = $watermarkText;
        return $this;
    }

    public function getWatermarkPosition(): ?string
    {
        return $this->watermarkPosition;
    }

    public function setWatermarkPosition(?string $watermarkPosition): static
    {
        $this->watermarkPosition = $watermarkPosition;
        return $this;
    }

    public function getWatermarkOpacity(): ?int
    {
        return $this->watermarkOpacity;
    }

    public function setWatermarkOpacity(?int $watermarkOpacity): static
    {
        $this->watermarkOpacity = $watermarkOpacity;
        return $this;
    }

    public function getMaxDeviceCount(): int
    {
        return $this->maxDeviceCount;
    }

    public function setMaxDeviceCount(int $maxDeviceCount): static
    {
        $this->maxDeviceCount = $maxDeviceCount;
        return $this;
    }

    public function getPlayAuthDuration(): int
    {
        return $this->playAuthDuration;
    }

    public function setPlayAuthDuration(int $playAuthDuration): static
    {
        $this->playAuthDuration = $playAuthDuration;
        return $this;
    }

    public function isEnableResume(): bool
    {
        return $this->enableResume;
    }

    public function setEnableResume(bool $enableResume): static
    {
        $this->enableResume = $enableResume;
        return $this;
    }

    public function getMinWatchDuration(): ?int
    {
        return $this->minWatchDuration;
    }

    public function setMinWatchDuration(?int $minWatchDuration): static
    {
        $this->minWatchDuration = $minWatchDuration;
        return $this;
    }

    public function getProgressCheckInterval(): ?int
    {
        return $this->progressCheckInterval;
    }

    public function setProgressCheckInterval(?int $progressCheckInterval): static
    {
        $this->progressCheckInterval = $progressCheckInterval;
        return $this;
    }

    public function isAllowSeeking(): bool
    {
        return $this->allowSeeking;
    }

    public function setAllowSeeking(bool $allowSeeking): static
    {
        $this->allowSeeking = $allowSeeking;
        return $this;
    }

    public function isAllowContextMenu(): bool
    {
        return $this->allowContextMenu;
    }

    public function setAllowContextMenu(bool $allowContextMenu): static
    {
        $this->allowContextMenu = $allowContextMenu;
        return $this;
    }

    public function isAllowDownload(): bool
    {
        return $this->allowDownload;
    }

    public function setAllowDownload(bool $allowDownload): static
    {
        $this->allowDownload = $allowDownload;
        return $this;
    }

    public function getExtendedConfig(): ?array
    {
        return $this->extendedConfig;
    }

    public function setExtendedConfig(?array $extendedConfig): static
    {
        $this->extendedConfig = $extendedConfig;
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
     * 获取播放控制配置
     */
    public function getPlayControlConfig(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'allow_fast_forward' => $this->isAllowFastForward(),
            'allow_speed_control' => $this->isAllowSpeedControl(),
            'allowed_speeds' => $this->getAllowedSpeeds() ?? [0.5, 0.75, 1.0, 1.25, 1.5, 2.0],
            'enable_watermark' => $this->isEnableWatermark(),
            'watermark' => [
                'text' => $this->getWatermarkText(),
                'position' => $this->getWatermarkPosition(),
                'opacity' => $this->getWatermarkOpacity(),
            ],
            'max_device_count' => $this->getMaxDeviceCount(),
            'play_auth_duration' => $this->getPlayAuthDuration(),
            'enable_resume' => $this->isEnableResume(),
            'min_watch_duration' => $this->getMinWatchDuration(),
            'progress_check_interval' => $this->getProgressCheckInterval(),
            'allow_seeking' => $this->isAllowSeeking(),
            'allow_context_menu' => $this->isAllowContextMenu(),
            'allow_download' => $this->isAllowDownload(),
            'extended_config' => $this->getExtendedConfig(),
        ];
    }

    /**
     * 检查是否启用严格模式（防快进+防拖拽）
     */
    public function isStrictMode(): bool
    {
        return !$this->isAllowFastForward() && !$this->isAllowSeeking();
    }

    /**
     * 获取水印配置
     */
    public function getWatermarkConfig(): array
    {
        if (!$this->isEnableWatermark()) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'text' => $this->getWatermarkText() ?: '培训课程',
            'position' => $this->getWatermarkPosition() ?: 'bottom-right',
            'opacity' => $this->getWatermarkOpacity() ?: 50,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 