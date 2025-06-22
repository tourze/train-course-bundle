<?php

namespace Tourze\TrainCourseBundle\Entity;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\LessonRepository;
use Tourze\TrainCourseBundle\Trait\SortableTrait;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;
use Tourze\TrainCourseBundle\Trait\UniqueCodeAware;


#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\Table(name: 'job_training_course_lesson', options: ['comment' => '课时信息'])]
#[ORM\UniqueConstraint(name: 'job_training_course_lesson_idx_uniq', columns: ['chapter_id', 'title'])]
class Lesson implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use UniqueCodeAware;
    use SortableTrait;
    use TimestampableTrait;
    use BlameableAware;

    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Chapter $chapter;

    #[ORM\Column(length: 120, options: ['comment' => '课时名称'])]
    private string $title;

    #[Groups(['admin_curd'])]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '课时封面'])]
    private ?string $coverThumb = null;

    #[ORM\Column(options: ['comment' => '视频时长（秒）'])]
    private ?int $durationSecond = null;

    #[ORM\Column(options: ['comment' => '人脸识别间隔(秒)'])]
    private int $faceDetectDuration = 900;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '视频地址'])]
    private ?string $videoUrl = null;

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return $this->getTitle();
    }

    public function getId(): ?string
    {
        return $this->id;
    }


    public function getChapter(): Chapter
    {
        return $this->chapter;
    }

    public function setChapter(Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCoverThumb(): ?string
    {
        return $this->coverThumb;
    }

    public function setCoverThumb(?string $coverThumb): static
    {
        $this->coverThumb = $coverThumb;

        return $this;
    }

    public function getDurationSecond(): ?int
    {
        return $this->durationSecond;
    }

    public function setDurationSecond(int $durationSecond): static
    {
        $this->durationSecond = $durationSecond;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getFaceDetectDuration(): int
    {
        return $this->faceDetectDuration;
    }

    public function setFaceDetectDuration(int $faceDetectDuration): static
    {
        $this->faceDetectDuration = $faceDetectDuration;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        $durationText = CarbonImmutable::today()->addSeconds($this->getDurationSecond())->format('H:i:s');

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
        return round($this->getDurationSecond() / 60 / 45, 2);
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            ...$this->retrieveSortableArray(),
            'coverThumb' => $this->getCoverThumb(),
            'durationSecond' => $this->getDurationSecond(),
            'faceDetectDuration' => $this->getFaceDetectDuration(),
            'videoUrl' => $this->getVideoUrl(),
        ];
    }
}
