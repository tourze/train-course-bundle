<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Enum\LessonLearnStatus;

class LessonLearnStatusTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        $cases = LessonLearnStatus::cases();
        
        $this->assertCount(4, $cases);
        $this->assertContainsOnlyInstancesOf(LessonLearnStatus::class, $cases);
    }
}