<?php

namespace Tourze\TrainCourseBundle\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * 唯一代码功能 Trait
 */
trait UniqueCodeAware
{
    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '唯一代码'])]
    private ?string $uniqueCode = null;

    public function getUniqueCode(): ?string
    {
        return $this->uniqueCode;
    }

    public function setUniqueCode(?string $uniqueCode): void
    {
        $this->uniqueCode = $uniqueCode;
    }

    public function generateUniqueCode(): string
    {
        if (null === $this->uniqueCode) {
            $this->uniqueCode = uniqid('', true);
        }

        return $this->uniqueCode;
    }
}
