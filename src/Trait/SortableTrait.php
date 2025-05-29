<?php

namespace Tourze\TrainCourseBundle\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;

/**
 * 排序功能 Trait
 */
trait SortableTrait
{
    #[ListColumn(title: '排序', order: 96)]
    #[FormField(title: '排序', span: 6, order: 96)]
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