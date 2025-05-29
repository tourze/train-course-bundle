<?php

namespace Tourze\TrainCourseBundle\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;

/**
 * 唯一代码功能 Trait
 */
trait UniqueCodeAware
{
    #[ListColumn(title: '唯一代码', order: 95)]
    #[FormField(title: '唯一代码', span: 8, order: 95)]
    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '唯一代码'])]
    private ?string $uniqueCode = null;

    public function getUniqueCode(): ?string
    {
        return $this->uniqueCode;
    }

    public function setUniqueCode(?string $uniqueCode): self
    {
        $this->uniqueCode = $uniqueCode;

        return $this;
    }

    public function generateUniqueCode(): string
    {
        if (null === $this->uniqueCode) {
            $this->uniqueCode = uniqid('', true);
        }

        return $this->uniqueCode;
    }
} 