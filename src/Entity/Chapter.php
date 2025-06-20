<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Copyable;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Column\CopyColumn;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Trait\SortableTrait;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;
use Tourze\TrainCourseBundle\Trait\UniqueCodeAware;

#[Copyable]
#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[ORM\Table(name: 'job_training_course_chapter', options: ['comment' => '课程章节'])]
#[ORM\UniqueConstraint(name: 'job_training_course_chapter_idx_uniq', columns: ['course_id', 'title'])]
class Chapter implements \Stringable, ApiArrayInterface
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

    #[Ignore]
    #[CopyColumn]
    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[CopyColumn(suffix: true)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, options: ['comment' => '章节标题'])]
    private string $title;

    #[Ignore]
    #[CurdAction(label: '课时', drawerWidth: 1200)]
    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: Lesson::class, orphanRemoval: true)]
    #[ORM\OrderBy(['sortNumber' => 'DESC', 'id' => 'ASC'])]
    private Collection $lessons;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
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

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): static
    {
        $this->course = $course;

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

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setChapter($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getChapter() === $this) {
                $lesson->setChapter(null);
            }
        }

        return $this;
    }

    public function getLessonCount(): int
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getLessons() as $lesson) {
            if ($lesson->getVideoUrl()) {
                ++$result;
            }
        }

        return $result;
    }

    public function getLessonTime(): float
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getLessons() as $lesson) {
            if ($lesson->getVideoUrl()) {
                $result += $lesson->getLessonTime();
            }
        }

        return round($result, 2);
    }

    public function getDurationSecond(): int
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getLessons() as $lesson) {
            if ($lesson->getVideoUrl()) {
                $result += $lesson->getDurationSecond();
            }
        }

        return $result;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
        ];
    }
}
