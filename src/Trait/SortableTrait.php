<?php

namespace Tourze\TrainCourseBundle\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * 排序功能 Trait
 */
trait SortableTrait
{
    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '排序号', 'default' => 0])]
    private int $sortNumber = 0;

    public function getSortNumber(): int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(int $sortNumber): self
    {
        $this->sortNumber = $sortNumber;

        return $this;
    }

    public function retrieveSortableArray(): array
    {
        return [
            'sortNumber' => $this->getSortNumber(),
        ];
    }
} 