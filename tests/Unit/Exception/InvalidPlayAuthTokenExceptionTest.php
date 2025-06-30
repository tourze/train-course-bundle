<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Exception\InvalidPlayAuthTokenException;

class InvalidPlayAuthTokenExceptionTest extends TestCase
{
    public function test_exception_can_be_thrown(): void
    {
        $this->expectException(InvalidPlayAuthTokenException::class);
        $this->expectExceptionMessage('Test message');
        
        throw new InvalidPlayAuthTokenException('Test message');
    }
    
    public function test_exception_is_runtime_exception(): void
    {
        $exception = new InvalidPlayAuthTokenException('Test');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}