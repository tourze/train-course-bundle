<?php

namespace Tourze\TrainCourseBundle\Entity;

use AntdCpBundle\Builder\Action\ModalWebViewAction;
use AntdCpBundle\Builder\Field\LongTextField;
use App\Kernel;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Copyable;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\ListAction;
use Tourze\EasyAdmin\Attribute\Column\CopyColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Column\PictureColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Field\ImagePickerField;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\TrainCourseBundle\Repository\LessonRepository;
use Tourze\TrainCourseBundle\Trait\SortableTrait;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;
use Tourze\TrainCourseBundle\Trait\UniqueCodeAware;


#[Copyable]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\Table(name: 'job_training_course_lesson', options: ['comment' => '课时信息'])]
#[ORM\UniqueConstraint(name: 'job_training_course_lesson_idx_uniq', columns: ['chapter_id', 'title'])]
class Lesson implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use UniqueCodeAware;
    use SortableTrait;
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

    #[CopyColumn]
    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Chapter $chapter;

    #[CopyColumn(suffix: true)]
    #[Keyword]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 120, options: ['comment' => '课时名称'])]
    private string $title;

    #[ImagePickerField]
    #[PictureColumn]
    #[CopyColumn]
    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[FormField(span: 6)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '课时封面'])]
    private ?string $coverThumb = null;

    #[CopyColumn]
    #[ListColumn]
    #[FormField(span: 9)]
    #[ORM\Column(options: ['comment' => '视频时长（秒）'])]
    private ?int $durationSecond = null;

    #[CopyColumn]
    #[FormField(span: 9)]
    #[ORM\Column(options: ['comment' => '人脸识别间隔(秒)'])]
    private int $faceDetectDuration = 900;

    /**
     * @LongTextField()
     */
    #[CopyColumn]
    #[FormField]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '视频地址'])]
    private ?string $videoUrl = null;







    public function __construct()
    {
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return $this->getTitle();
    }

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
        $durationText = Carbon::today()->addSeconds($this->getDurationSecond())->format('H:i:s');

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
    #[ListColumn(title: '学时')]
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
            'videoPlayUrl' => $this->getVideoPlayUrl(),
        ];
    }

    public function getVideoPlayUrl(): string
    {
        return Kernel::getInstance()
            ->getContainer()
            ->get(UrlGeneratorInterface::class)
            ->generate('job-training-player-video', ['lessonId' => $this->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    #[ListAction(title: '视频播放')]
    public function renderVideoAction()
    {
        return ModalWebViewAction::gen()
            ->setIframeUrlDataIndex('videoPlayUrl')
            ->setLabel('视频播放')
            ->setIsLinkMode(true)
            ->setContainerStyle((object) [
                'height' => 700,
            ])
            ->setType('modal-web-view-action')
            ->setModalWidth(900);
    }


}
