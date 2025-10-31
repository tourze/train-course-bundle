<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainCourseBundle\Enum\LessonLearnStatus;

/**
 * @internal
 */
#[CoversClass(LessonLearnStatus::class)]
final class LessonLearnStatusTest extends AbstractEnumTestCase
{
    #[Test]
    public function testEnumCasesCount(): void
    {
        $cases = LessonLearnStatus::cases();
        self::assertCount(4, $cases);
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('toArrayProvider')]
    public function testToArray(LessonLearnStatus $enum, array $expected): void
    {
        $result = $enum->toArray();

        // $result is already known to be array from method signature
        self::assertCount(2, $result);
        self::assertArrayHasKey('value', $result);
        self::assertArrayHasKey('label', $result);
        self::assertSame($expected['value'], $result['value']);
        self::assertSame($expected['label'], $result['label']);
    }

    /** @return array<int, array{0: LessonLearnStatus, 1: array<string, string>}> */
    public static function toArrayProvider(): array
    {
        return [
            [LessonLearnStatus::NOT_BUY, ['value' => 'not_buy', 'label' => '未购买']],
            [LessonLearnStatus::PENDING, ['value' => 'pending', 'label' => '待学习']],
            [LessonLearnStatus::LEARNING, ['value' => 'learning', 'label' => '学习中']],
            [LessonLearnStatus::FINISHED, ['value' => 'finished', 'label' => '已完成']],
        ];
    }

    #[Test]
    public function testValueUniqueness(): void
    {
        $values = array_map(fn (LessonLearnStatus $case) => $case->value, LessonLearnStatus::cases());
        $uniqueValues = array_unique($values);

        self::assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    #[Test]
    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (LessonLearnStatus $case) => $case->getLabel(), LessonLearnStatus::cases());
        $uniqueLabels = array_unique($labels);

        self::assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
    }
}
