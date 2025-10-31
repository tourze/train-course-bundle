<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainCourseBundle\Exception\AuthDataEncodeException;

/**
 * AuthDataEncodeException 测试
 *
 * @internal
 */
#[CoversClass(AuthDataEncodeException::class)]
final class AuthDataEncodeExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
    }

    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new AuthDataEncodeException();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithMessage(): void
    {
        $message = '认证数据编码失败';
        $exception = new AuthDataEncodeException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = '认证数据编码失败';
        $code = 500;
        $exception = new AuthDataEncodeException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \InvalidArgumentException('原始错误');
        $exception = new AuthDataEncodeException('认证数据编码失败', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testInheritanceHierarchy(): void
    {
        $reflection = new \ReflectionClass(AuthDataEncodeException::class);

        $this->assertTrue($reflection->isSubclassOf(\RuntimeException::class));
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
        $this->assertTrue($reflection->implementsInterface(\Throwable::class));
    }
}
