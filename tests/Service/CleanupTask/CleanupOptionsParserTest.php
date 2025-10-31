<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CleanupTask;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Tourze\TrainCourseBundle\Service\CleanupTask\CleanupOptionsParser;

/**
 * @internal
 */
#[CoversClass(CleanupOptionsParser::class)]
final class CleanupOptionsParserTest extends TestCase
{
    private CleanupOptionsParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CleanupOptionsParser();
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CleanupOptionsParser::class, $this->parser);
    }

    public function testParseCleanupOptionsWithNoOptionsReturnsAllTrue(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturn(false);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertTrue($result['clearCache']);
        $this->assertTrue($result['cleanupVersions']);
        $this->assertTrue($result['cleanupAudits']);
        $this->assertTrue($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsWithClearCacheOnly(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['clear-cache', true],
            ['cleanup-versions', false],
            ['cleanup-audits', false],
            ['cleanup-expired', false],
        ]);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertTrue($result['clearCache']);
        $this->assertFalse($result['cleanupVersions']);
        $this->assertFalse($result['cleanupAudits']);
        $this->assertFalse($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsWithCleanupVersionsOnly(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['clear-cache', false],
            ['cleanup-versions', true],
            ['cleanup-audits', false],
            ['cleanup-expired', false],
        ]);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertFalse($result['clearCache']);
        $this->assertTrue($result['cleanupVersions']);
        $this->assertFalse($result['cleanupAudits']);
        $this->assertFalse($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsWithCleanupAuditsOnly(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['clear-cache', false],
            ['cleanup-versions', false],
            ['cleanup-audits', true],
            ['cleanup-expired', false],
        ]);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertFalse($result['clearCache']);
        $this->assertFalse($result['cleanupVersions']);
        $this->assertTrue($result['cleanupAudits']);
        $this->assertFalse($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsWithCleanupExpiredOnly(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['clear-cache', false],
            ['cleanup-versions', false],
            ['cleanup-audits', false],
            ['cleanup-expired', true],
        ]);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertFalse($result['clearCache']);
        $this->assertFalse($result['cleanupVersions']);
        $this->assertFalse($result['cleanupAudits']);
        $this->assertTrue($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsWithMultipleOptions(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['clear-cache', true],
            ['cleanup-versions', true],
            ['cleanup-audits', false],
            ['cleanup-expired', false],
        ]);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertTrue($result['clearCache']);
        $this->assertTrue($result['cleanupVersions']);
        $this->assertFalse($result['cleanupAudits']);
        $this->assertFalse($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsWithAllOptions(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([
            ['clear-cache', true],
            ['cleanup-versions', true],
            ['cleanup-audits', true],
            ['cleanup-expired', true],
        ]);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertTrue($result['clearCache']);
        $this->assertTrue($result['cleanupVersions']);
        $this->assertTrue($result['cleanupAudits']);
        $this->assertTrue($result['cleanupExpired']);
    }

    public function testParseCleanupOptionsReturnsArrayWithCorrectKeys(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturn(false);

        $result = $this->parser->parseCleanupOptions($input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('clearCache', $result);
        $this->assertArrayHasKey('cleanupVersions', $result);
        $this->assertArrayHasKey('cleanupAudits', $result);
        $this->assertArrayHasKey('cleanupExpired', $result);
    }
}
