<?php

namespace Tourze\TrainCourseBundle\Entity;

use Carbon\CarbonImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineHelper\SortableTrait;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\LessonRepository;
use Tourze\TrainCourseBundle\Trait\UniqueCodeAware;

/**
 * @implements ApiArrayInterface<string, mixed>
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\Table(name: 'job_training_course_lesson', options: ['comment' => '课时信息'])]
#[ORM\UniqueConstraint(name: 'job_training_course_lesson_idx_uniq', columns: ['chapter_id', 'title'])]
class Lesson implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use SnowflakeKeyAware;
    use UniqueCodeAware;
    use SortableTrait;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Chapter $chapter;

    #[ORM\Column(length: 120, options: ['comment' => '课时名称'])]
    #[Assert\NotBlank(message: '课时名称不能为空')]
    #[Assert\Length(max: 120, maxMessage: '课时名称长度不能超过 {{ limit }} 个字符')]
    private string $title;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '课时封面'])]
    #[Assert\Length(max: 255, maxMessage: '课时封面URL长度不能超过 {{ limit }} 个字符')]
    #[Assert\Url(message: '课时封面必须是有效的URL地址')]
    private ?string $coverThumb = null;

    #[ORM\Column(options: ['comment' => '视频时长（秒）'])]
    #[Assert\PositiveOrZero(message: '视频时长必须大于或等于0')]
    #[Assert\Range(min: 0, max: 86400, notInRangeMessage: '视频时长必须在 {{ min }} 到 {{ max }} 秒之间')]
    private ?int $durationSecond = null;

    #[ORM\Column(options: ['comment' => '人脸识别间隔(秒)'])]
    #[Assert\Positive(message: '人脸识别间隔必须大于0')]
    #[Assert\Range(min: 60, max: 3600, notInRangeMessage: '人脸识别间隔必须在 {{ min }} 到 {{ max }} 秒之间')]
    private int $faceDetectDuration = 900;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '视频地址'])]
    #[Assert\Length(max: 255, maxMessage: '视频地址长度不能超过 {{ limit }} 个字符')]
    #[Assert\Url(message: '视频地址必须是有效的URL地址')]
    private ?string $videoUrl = null;

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return $this->getTitle();
    }

    public function getChapter(): Chapter
    {
        return $this->chapter;
    }

    public function setChapter(Chapter $chapter): void
    {
        $this->chapter = $chapter;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCoverThumb(): ?string
    {
        return $this->coverThumb;
    }

    public function setCoverThumb(?string $coverThumb): void
    {
        $this->coverThumb = $coverThumb;
    }

    public function getDurationSecond(): ?int
    {
        return $this->durationSecond;
    }

    public function setDurationSecond(int $durationSecond): void
    {
        $this->durationSecond = $durationSecond;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): void
    {
        $this->videoUrl = $videoUrl;
    }

    public function getFaceDetectDuration(): int
    {
        return $this->faceDetectDuration;
    }

    public function setFaceDetectDuration(int $faceDetectDuration): void
    {
        $this->faceDetectDuration = $faceDetectDuration;
    }

    /** @return array<string, mixed> */
    public function retrieveApiArray(): array
    {
        $durationText = CarbonImmutable::today()->addSeconds($this->getDurationSecond() ?? 0)->format('H:i:s');

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'coverThumb' => $this->getCoverThumb(),
            'durationSecond' => $this->getDurationSecond(),
            'durationText' => $durationText,
            'videoUrl' => $this->getVideoUrl(),
        ];
    }

    /**
     * 学时计算，学时是按照45分钟一节来计算的
     */
    public function getLessonTime(): float
    {
        return round(($this->getDurationSecond() ?? 0) / 60 / 45, 2);
    }

    /** @return array<string, mixed> */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'coverThumb' => $this->getCoverThumb(),
            'durationSecond' => $this->getDurationSecond(),
            'faceDetectDuration' => $this->getFaceDetectDuration(),
            'videoUrl' => $this->getVideoUrl(),
            'sortNumber' => $this->getSortNumber(),
        ];
    }
}
