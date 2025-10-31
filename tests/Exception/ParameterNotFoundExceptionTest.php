<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainCourseBundle\Exception\ParameterNotFoundException;

/**
 * ParameterNotFoundException 单元测试
 *
 * @internal
 */
#[CoversClass(ParameterNotFoundException::class)]
final class ParameterNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
    }

    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new ParameterNotFoundException();
        // 测试异常是否正确继承
        $this->assertEquals('RuntimeException', get_parent_class($exception));
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test parameter not found';
        $exception = new ParameterNotFoundException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Test parameter not found';
        $code = 404;
        $exception = new ParameterNotFoundException($message, $code);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new ParameterNotFoundException('Test message', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
