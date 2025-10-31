<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\CoursePlayControlRepository;

/**
 * 课程播放控制实体
 *
 * 管理课程的播放控制策略，包括防快进、倍速控制、水印设置等
 * 确保学习过程的完整性和合规性
 */
#[ORM\Entity(repositoryClass: CoursePlayControlRepository::class)]
#[ORM\Table(name: 'train_course_play_control', options: ['comment' => '课程播放控制'])]
class CoursePlayControl implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\OneToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用控制', 'default' => true])]
    #[Assert\NotNull]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否允许快进', 'default' => false])]
    #[Assert\NotNull]
    private bool $allowFastForward = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否允许倍速控制', 'default' => false])]
    #[Assert\NotNull]
    private bool $allowSpeedControl = false;

    /**
     * @var array<float>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '允许的播放倍速'])]
    #[Assert\Type(type: 'array')]
    private ?array $allowedSpeeds = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用水印', 'default' => true])]
    #[Assert\NotNull]
    private bool $enableWatermark = true;

    #[ORM\Column(length: 200, nullable: true, options: ['comment' => '水印文本'])]
    #[Assert\Length(max: 200)]
    private ?string $watermarkText = null;

    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '水印位置'])]
    #[Assert\Length(max: 50)]
    private ?string $watermarkPosition = 'bottom-right';

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '水印透明度（0-100）'])]
    #[Assert\Range(min: 0, max: 100)]
    private ?int $watermarkOpacity = 50;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '最大设备数', 'default' => 3])]
    #[Assert\PositiveOrZero]
    private int $maxDeviceCount = 3;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '播放凭证有效期（秒）', 'default' => 3600])]
    #[Assert\PositiveOrZero]
    private int $playAuthDuration = 3600;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用续播', 'default' => true])]
    #[Assert\NotNull]
    private bool $enableResume = true;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '最小观看时长（秒）'])]
    #[Assert\PositiveOrZero]
    private ?int $minWatchDuration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '观看进度检查间隔（秒）'])]
    #[Assert\PositiveOrZero]
    private ?int $progressCheckInterval = 30;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否允许拖拽', 'default' => false])]
    #[Assert\NotNull]
    private bool $allowSeeking = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用右键菜单', 'default' => false])]
    #[Assert\NotNull]
    private bool $allowContextMenu = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否允许下载', 'default' => false])]
    #[Assert\NotNull]
    private bool $allowDownload = false;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展配置'])]
    #[Assert\Type(type: 'array')]
    private ?array $extendedConfig = null;

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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isAllowFastForward(): bool
    {
        return $this->allowFastForward;
    }

    public function setAllowFastForward(bool $allowFastForward): void
    {
        $this->allowFastForward = $allowFastForward;
    }

    public function isAllowSpeedControl(): bool
    {
        return $this->allowSpeedControl;
    }

    public function setAllowSpeedControl(bool $allowSpeedControl): void
    {
        $this->allowSpeedControl = $allowSpeedControl;
    }

    /** @return array<float>|null */
    public function getAllowedSpeeds(): ?array
    {
        return $this->allowedSpeeds;
    }

    /** @param array<float>|null $allowedSpeeds */
    public function setAllowedSpeeds(?array $allowedSpeeds): void
    {
        $this->allowedSpeeds = $allowedSpeeds;
    }

    public function isEnableWatermark(): bool
    {
        return $this->enableWatermark;
    }

    public function setEnableWatermark(bool $enableWatermark): void
    {
        $this->enableWatermark = $enableWatermark;
    }

    public function getWatermarkText(): ?string
    {
        return $this->watermarkText;
    }

    public function setWatermarkText(?string $watermarkText): void
    {
        $this->watermarkText = $watermarkText;
    }

    public function getWatermarkPosition(): ?string
    {
        return $this->watermarkPosition;
    }

    public function setWatermarkPosition(?string $watermarkPosition): void
    {
        $this->watermarkPosition = $watermarkPosition;
    }

    public function getWatermarkOpacity(): ?int
    {
        return $this->watermarkOpacity;
    }

    public function setWatermarkOpacity(?int $watermarkOpacity): void
    {
        $this->watermarkOpacity = $watermarkOpacity;
    }

    public function getMaxDeviceCount(): int
    {
        return $this->maxDeviceCount;
    }

    public function setMaxDeviceCount(int $maxDeviceCount): void
    {
        $this->maxDeviceCount = $maxDeviceCount;
    }

    public function getPlayAuthDuration(): int
    {
        return $this->playAuthDuration;
    }

    public function setPlayAuthDuration(int $playAuthDuration): void
    {
        $this->playAuthDuration = $playAuthDuration;
    }

    public function isEnableResume(): bool
    {
        return $this->enableResume;
    }

    public function setEnableResume(bool $enableResume): void
    {
        $this->enableResume = $enableResume;
    }

    public function getMinWatchDuration(): ?int
    {
        return $this->minWatchDuration;
    }

    public function setMinWatchDuration(?int $minWatchDuration): void
    {
        $this->minWatchDuration = $minWatchDuration;
    }

    public function getProgressCheckInterval(): ?int
    {
        return $this->progressCheckInterval;
    }

    public function setProgressCheckInterval(?int $progressCheckInterval): void
    {
        $this->progressCheckInterval = $progressCheckInterval;
    }

    public function isAllowSeeking(): bool
    {
        return $this->allowSeeking;
    }

    public function setAllowSeeking(bool $allowSeeking): void
    {
        $this->allowSeeking = $allowSeeking;
    }

    public function isAllowContextMenu(): bool
    {
        return $this->allowContextMenu;
    }

    public function setAllowContextMenu(bool $allowContextMenu): void
    {
        $this->allowContextMenu = $allowContextMenu;
    }

    public function isAllowDownload(): bool
    {
        return $this->allowDownload;
    }

    public function setAllowDownload(bool $allowDownload): void
    {
        $this->allowDownload = $allowDownload;
    }

    /** @return array<string, mixed>|null */
    public function getExtendedConfig(): ?array
    {
        return $this->extendedConfig;
    }

    /** @param array<string, mixed>|null $extendedConfig */
    public function setExtendedConfig(?array $extendedConfig): void
    {
        $this->extendedConfig = $extendedConfig;
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
     * 获取播放控制配置
     */
    /** @return array<string, mixed> */
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
    /** @return array<string, mixed> */
    public function getWatermarkConfig(): array
    {
        if (!$this->isEnableWatermark()) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'text' => $this->getWatermarkText() ?? '培训课程',
            'position' => $this->getWatermarkPosition() ?? 'bottom-right',
            'opacity' => $this->getWatermarkOpacity() ?? 50,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
