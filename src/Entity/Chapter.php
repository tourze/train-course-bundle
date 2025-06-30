<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\ChapterRepository;
use Tourze\TrainCourseBundle\Trait\SortableTrait;
use Tourze\TrainCourseBundle\Trait\TimestampableTrait;
use Tourze\TrainCourseBundle\Trait\UniqueCodeAware;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[ORM\Table(name: 'job_training_course_chapter', options: ['comment' => '课程章节'])]
#[ORM\UniqueConstraint(name: 'job_training_course_chapter_idx_uniq', columns: ['course_id', 'title'])]
class Chapter implements \Stringable, ApiArrayInterface
{
    use SnowflakeKeyAware;
    use UniqueCodeAware;
    use SortableTrait;
    use TimestampableTrait;
    use BlameableAware;


    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, options: ['comment' => '章节标题'])]
    private string $title;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: Lesson::class, orphanRemoval: true)]
    #[ORM\OrderBy(value: ['sortNumber' => 'DESC', 'id' => 'ASC'])]
    private Collection $lessons;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return $this->getTitle();
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
        $this->lessons->removeElement($lesson);

        return $this;
    }

    public function getLessonCount(): int
    {
        // 这里只统计有效的
        $result = 0;
        foreach ($this->getLessons() as $lesson) {
            if (null !== $lesson->getVideoUrl()) {
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
            if (null !== $lesson->getVideoUrl()) {
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
            if (null !== $lesson->getVideoUrl()) {
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
