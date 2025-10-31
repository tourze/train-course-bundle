<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainCourseBundle\Exception\InvalidPlayAuthTokenException;

/**
 * @internal
 */
#[CoversClass(InvalidPlayAuthTokenException::class)]
final class InvalidPlayAuthTokenExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
    }

    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(InvalidPlayAuthTokenException::class);
        $this->expectExceptionMessage('Test message');

        throw new InvalidPlayAuthTokenException('Test message');
    }

    public function testExceptionIsRuntimeException(): void
    {
        $exception = new InvalidPlayAuthTokenException('Test');

        $this->assertSame('Test', $exception->getMessage());
    }
}
