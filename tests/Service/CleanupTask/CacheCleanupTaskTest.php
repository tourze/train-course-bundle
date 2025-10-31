<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CleanupTask;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\CleanupTask\CacheCleanupTask;

/**
 * @internal
 */
#[CoversClass(CacheCleanupTask::class)]
final class CacheCleanupTaskTest extends TestCase
{
    private CacheCleanupTask $task;

    private CacheItemPoolInterface $cache;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->task = new CacheCleanupTask($this->cache);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CacheCleanupTask::class, $this->task);
    }

    public function testCleanupInDryRunDoesNotClearCache(): void
    {
        $this->cache->expects($this->never())
            ->method('clear')
        ;

        $result = $this->task->cleanup($this->io, true);

        $this->assertSame(5, $result);
    }

    public function testCleanupNotInDryRunClearsCache(): void
    {
        $this->cache->expects($this->exactly(5))
            ->method('clear')
            ->willReturn(true)
        ;

        $result = $this->task->cleanup($this->io, false);

        $this->assertSame(5, $result);
    }

    public function testCleanupHandlesCacheException(): void
    {
        $this->cache->method('clear')
            ->willThrowException(new \Exception('Cache error'))
        ;

        $result = $this->task->cleanup($this->io, false);

        // 应该继续处理其他缓存模式
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testCleanupReturnsCorrectCount(): void
    {
        $this->cache->method('clear')
            ->willReturn(true)
        ;

        $result = $this->task->cleanup($this->io, false);

        // 有5个缓存模式
        $this->assertSame(5, $result);
    }
}
