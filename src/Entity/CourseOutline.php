<?php

namespace Tourze\TrainCourseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainCourseBundle\Repository\CourseOutlineRepository;

/**
 * 课程大纲实体
 *
 * 存储课程的详细大纲信息，包括学习目标、内容要点、考核标准等
 * 支持结构化的课程内容组织和管理
 */
#[ORM\Entity(repositoryClass: CourseOutlineRepository::class)]
#[ORM\Table(name: 'train_course_outline', options: ['comment' => '课程大纲'])]
class CourseOutline implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'outlines')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => '关联课程'])]
    #[Assert\NotNull(message: '关联课程不能为空')]
    private ?Course $course = null;

    #[ORM\Column(length: 255, options: ['comment' => '大纲标题'])]
    #[Assert\NotBlank(message: '大纲标题不能为空')]
    #[Assert\Length(max: 255, maxMessage: '大纲标题长度不能超过 {{ limit }} 个字符')]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '学习目标'])]
    #[Assert\Length(max: 65535, maxMessage: '学习目标长度不能超过 {{ limit }} 个字符')]
    private ?string $learningObjectives = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '内容要点'])]
    #[Assert\Length(max: 65535, maxMessage: '内容要点长度不能超过 {{ limit }} 个字符')]
    private ?string $contentPoints = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '重点难点'])]
    #[Assert\Length(max: 65535, maxMessage: '重点难点长度不能超过 {{ limit }} 个字符')]
    private ?string $keyDifficulties = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '考核标准'])]
    #[Assert\Length(max: 65535, maxMessage: '考核标准长度不能超过 {{ limit }} 个字符')]
    private ?string $assessmentCriteria = null;

    #[ORM\Column(name: 'reference_materials', type: Types::TEXT, nullable: true, options: ['comment' => '参考资料'])]
    #[Assert\Length(max: 65535, maxMessage: '参考资料长度不能超过 {{ limit }} 个字符')]
    private ?string $references = null;

    #[ORM\Column(nullable: true, options: ['comment' => '预计学习时长(分钟)'])]
    #[Assert\PositiveOrZero(message: '预计学习时长必须大于或等于0')]
    private ?int $estimatedMinutes = null;

    #[ORM\Column(options: ['comment' => '排序号', 'default' => 0])]
    #[Assert\PositiveOrZero(message: '排序号必须大于或等于0')]
    private int $sortNumber = 0;

    #[ORM\Column(length: 50, options: ['comment' => '状态', 'default' => 'draft'])]
    #[Assert\NotBlank(message: '状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '状态长度不能超过 {{ limit }} 个字符')]
    #[Assert\Choice(choices: ['draft', 'published', 'archived'], message: '状态必须是 draft、published 或 archived 之一')]
    private string $status = 'draft';

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '扩展属性'])]
    #[Assert\Type(type: 'array', message: '扩展属性必须是数组类型')]
    private ?array $metadata = null;

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLearningObjectives(): ?string
    {
        return $this->learningObjectives;
    }

    public function setLearningObjectives(?string $learningObjectives): void
    {
        $this->learningObjectives = $learningObjectives;
    }

    public function getContentPoints(): ?string
    {
        return $this->contentPoints;
    }

    public function setContentPoints(?string $contentPoints): void
    {
        $this->contentPoints = $contentPoints;
    }

    public function getKeyDifficulties(): ?string
    {
        return $this->keyDifficulties;
    }

    public function setKeyDifficulties(?string $keyDifficulties): void
    {
        $this->keyDifficulties = $keyDifficulties;
    }

    public function getAssessmentCriteria(): ?string
    {
        return $this->assessmentCriteria;
    }

    public function setAssessmentCriteria(?string $assessmentCriteria): void
    {
        $this->assessmentCriteria = $assessmentCriteria;
    }

    public function getReferences(): ?string
    {
        return $this->references;
    }

    public function setReferences(?string $references): void
    {
        $this->references = $references;
    }

    public function getEstimatedMinutes(): ?int
    {
        return $this->estimatedMinutes;
    }

    public function setEstimatedMinutes(?int $estimatedMinutes): void
    {
        $this->estimatedMinutes = $estimatedMinutes;
    }

    public function getSortNumber(): int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(int $sortNumber): void
    {
        $this->sortNumber = $sortNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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
     * 检查大纲是否已发布
     */
    public function isPublished(): bool
    {
        return 'published' === $this->status;
    }

    /**
     * 获取预计学时（小时）
     */
    public function getEstimatedHours(): float
    {
        return null !== $this->estimatedMinutes ? round($this->estimatedMinutes / 60, 2) : 0;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
