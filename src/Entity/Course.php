<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Copyable;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\CopyColumn;
use Tourze\EasyAdmin\Attribute\Column\PictureColumn;
use Tourze\EasyAdmin\Attribute\Field\ImagePickerField;
use Tourze\EasyAdmin\Attribute\Field\RichTextField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Trait\SortableTrait;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;
use Tourze\TrainCourseBundle\Trait\UniqueCodeAware;

#[Copyable]
#[Listable]
#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\Table(name: 'job_training_course', options: ['comment' => '课程信息'])]
class Course implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use UniqueCodeAware;
    use SortableTrait;
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

    #[TrackColumn]
    #[Groups(['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[CopyColumn]
    #[Filterable(label: '所属分类')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[CopyColumn(suffix: true)]
    #[ORM\Column(length: 120, options: ['comment' => '课程名称'])]
    private string $title;

    //    //    #[ORM\ManyToOne]
    private ?UserInterface $instructor = null;

    #[CopyColumn]
    #[ORM\Column(options: ['comment' => '有效期'])]
    private int $validDay = 365;

    #[CopyColumn]
    #[ORM\Column(options: ['comment' => '毕业学时'])]
    private ?int $learnHour = null;

    #[CopyColumn]
    #[ORM\Column(length: 30, nullable: true, options: ['comment' => '任课老师'])]
    private ?string $teacherName = null;

    #[ImagePickerField]
    #[PictureColumn]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '课程封面'])]
    private ?string $coverThumb = null;

    /**
     * @BraftEditor()
     */
    #[RichTextField]
    #[CopyColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '课程详情'])]
    private ?string $description = null;

    #[Ignore]
    #[CurdAction(label: '课程章节', drawerWidth: 1200)]
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Chapter::class, orphanRemoval: true)]
    #[ORM\OrderBy(['sortNumber' => 'DESC', 'id' => 'ASC'])]
    private Collection $chapters;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseOutline::class, orphanRemoval: true)]
    #[ORM\OrderBy(['sortNumber' => 'DESC', 'id' => 'ASC'])]
    private Collection $outlines;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Collect::class, orphanRemoval: true)]
    private Collection $collects;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Evaluate::class, orphanRemoval: true)]
    #[ORM\OrderBy(['createTime' => 'DESC'])]
    private Collection $evaluates;

    #[CopyColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '支付价格'])]
    private ?string $price = '20.00';





    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->outlines = new ArrayCollection();
        $this->collects = new ArrayCollection();
        $this->evaluates = new ArrayCollection();
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

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

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

    public function getInstructor(): ?UserInterface
    {
        return $this->instructor;
    }

    public function setInstructor(?UserInterface $instructor): static
    {
        $this->instructor = $instructor;

        return $this;
    }

    public function getValidDay(): int
    {
        return $this->validDay;
    }

    public function setValidDay(int $validDay): static
    {
        $this->validDay = $validDay;

        return $this;
    }

    public function getLearnHour(): ?int
    {
        return $this->learnHour;
    }

    public function setLearnHour(int $learnHour): static
    {
        $this->learnHour = $learnHour;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): static
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setCourse($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): static
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getCourse() === $this) {
                $chapter->setCourse(null);
            }
        }

        return $this;
    }



    public function getChapterCount(): int
    {
        return $this->getChapters()->count();
    }

    public function getLessonCount(): int
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getChapters() as $chapter) {
            $result += $chapter->getLessonCount();
        }

        return $result;
    }

    public function getLessonTime(): float
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getChapters() as $chapter) {
            $result += $chapter->getLessonTime();
        }

        return round($result, 2);
    }

    public function getDurationSecond(): int
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getChapters() as $chapter) {
            $result += $chapter->getDurationSecond();
        }

        return $result;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'category' => $this->getCategory()->retrieveApiArray(),
            'title' => $this->getTitle(),
            'instructor' => $this->getInstructor() ? [
                'id' => method_exists($this->getInstructor(), 'getId') ? $this->getInstructor()->getId() : null,
                'name' => method_exists($this->getInstructor(), 'getUserIdentifier') ? $this->getInstructor()->getUserIdentifier() : null,
            ] : null,
            'validDay' => $this->getValidDay(),
            'learnHour' => $this->getLearnHour(),
            'coverThumb' => $this->getCoverThumb(),
            'description' => $this->getDescription(),
            'plainDescription' => strip_tags($this->getDescription()),
            'lessonCount' => $this->getLessonCount(), // 课时
            'lessonTime' => $this->getLessonTime(), // 学时
            'chapterCount' => $this->getChapterCount(), // 章节数量
            'teacherName' => $this->getTeacherName(),
            'durationSecond' => $this->getDurationSecond(),
            'registrationCount' => 0,
        ];
    }

    public function getTeacherName(): ?string
    {
        return $this->teacherName;
    }

    public function setTeacherName(?string $teacherName): static
    {
        $this->teacherName = $teacherName;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }





    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'validDay' => $this->getValidDay(),
            'learnHour' => $this->getLearnHour(),
            'teacherName' => $this->getTeacherName(),
            'coverThumb' => $this->getCoverThumb(),
            'description' => $this->getDescription(),
            'price' => $this->getPrice(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return Collection<int, CourseOutline>
     */
    public function getOutlines(): Collection
    {
        return $this->outlines;
    }

    public function addOutline(CourseOutline $outline): static
    {
        if (!$this->outlines->contains($outline)) {
            $this->outlines->add($outline);
            $outline->setCourse($this);
        }

        return $this;
    }

    public function removeOutline(CourseOutline $outline): static
    {
        if ($this->outlines->removeElement($outline)) {
            // set the owning side to null (unless already changed)
            if ($outline->getCourse() === $this) {
                $outline->setCourse(null);
            }
        }

        return $this;
    }

    public function getOutlineCount(): int
    {
        return $this->getOutlines()->count();
    }

    /**
     * @return Collection<int, Collect>
     */
    public function getCollects(): Collection
    {
        return $this->collects;
    }

    public function addCollect(Collect $collect): static
    {
        if (!$this->collects->contains($collect)) {
            $this->collects->add($collect);
            $collect->setCourse($this);
        }

        return $this;
    }

    public function removeCollect(Collect $collect): static
    {
        if ($this->collects->removeElement($collect)) {
            // set the owning side to null (unless already changed)
            if ($collect->getCourse() === $this) {
                $collect->setCourse(null);
            }
        }

        return $this;
    }

    public function getCollectCount(): int
    {
        return $this->getCollects()->count();
    }

    /**
     * @return Collection<int, Evaluate>
     */
    public function getEvaluates(): Collection
    {
        return $this->evaluates;
    }

    public function addEvaluate(Evaluate $evaluate): static
    {
        if (!$this->evaluates->contains($evaluate)) {
            $this->evaluates->add($evaluate);
            $evaluate->setCourse($this);
        }

        return $this;
    }

    public function removeEvaluate(Evaluate $evaluate): static
    {
        if ($this->evaluates->removeElement($evaluate)) {
            // set the owning side to null (unless already changed)
            if ($evaluate->getCourse() === $this) {
                $evaluate->setCourse(null);
            }
        }

        return $this;
    }

    public function getEvaluateCount(): int
    {
        return $this->getEvaluates()->count();
    }

    /**
     * 获取课程平均评分
     */
    public function getAverageRating(): float
    {
        $publishedEvaluates = $this->evaluates->filter(function (Evaluate $evaluate) {
            return $evaluate->isPublished();
        });

        if ($publishedEvaluates->isEmpty()) {
            return 0;
        }

        $totalRating = 0;
        foreach ($publishedEvaluates as $evaluate) {
            $totalRating += $evaluate->getRating();
        }

        return round($totalRating / $publishedEvaluates->count(), 2);
    }


}
