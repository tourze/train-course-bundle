<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseDataSaver;

/**
 * @internal
 */
#[CoversClass(CourseDataSaver::class)]
final class CourseDataSaverTest extends TestCase
{
    private CourseDataSaver $saver;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->saver = new CourseDataSaver();
        $this->tempDir = sys_get_temp_dir() . '/test_backup_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0o755, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if (false !== $files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseDataSaver::class, $this->saver);
    }

    public function testSaveCourseDataCreatesFile(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '测试课程1',
                'description' => '课程描述',
            ],
            [
                'id' => 2,
                'title' => '测试课程2',
                'description' => '课程描述2',
            ],
        ];
        $filename = 'courses.json';

        $result = $this->saver->saveCourseData($this->tempDir, $courseData, $filename);

        $expectedPath = $this->tempDir . '/' . $filename;
        $this->assertSame($expectedPath, $result);
        $this->assertFileExists($expectedPath);
    }

    public function testSaveCourseDataWithCorrectContent(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '测试课程',
                'chapters' => [
                    ['id' => 1, 'title' => '第一章'],
                ],
            ],
        ];
        $filename = 'courses.json';

        $result = $this->saver->saveCourseData($this->tempDir, $courseData, $filename);

        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertCount(1, $decoded);
        $this->assertIsArray($decoded[0]);
        $this->assertSame('测试课程', $decoded[0]['title']);
        $this->assertArrayHasKey('chapters', $decoded[0]);
    }

    public function testSaveCourseDataWithEmptyArray(): void
    {
        $courseData = [];
        $filename = 'empty_courses.json';

        $result = $this->saver->saveCourseData($this->tempDir, $courseData, $filename);

        $this->assertFileExists($result);
        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertEmpty($decoded);
    }

    public function testSaveCourseDataPreservesUnicode(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '中文课程标题',
                'description' => '这是一个包含中文的描述',
            ],
        ];
        $filename = 'unicode_courses.json';

        $result = $this->saver->saveCourseData($this->tempDir, $courseData, $filename);

        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        // 验证中文没有被转义
        $this->assertStringContainsString('中文课程标题', $content);
        $this->assertStringContainsString('这是一个包含中文的描述', $content);
    }

    public function testSaveIncrementalDataCreatesFile(): void
    {
        $courseData = [
            ['id' => 1, 'title' => '课程1'],
            ['id' => 2, 'title' => '课程2'],
        ];

        $result = $this->saver->saveIncrementalData($this->tempDir, $courseData);

        $expectedPath = $this->tempDir . '/incremental_courses.json';
        $this->assertSame($expectedPath, $result);
        $this->assertFileExists($expectedPath);
    }

    public function testSaveIncrementalDataWithCorrectStructure(): void
    {
        $courseData = [
            ['id' => 1, 'title' => '更新的课程'],
        ];

        $result = $this->saver->saveIncrementalData($this->tempDir, $courseData);

        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('since', $decoded);
        $this->assertArrayHasKey('backup_time', $decoded);
        $this->assertArrayHasKey('courses', $decoded);

        $this->assertIsString($decoded['since']);
        $this->assertIsString($decoded['backup_time']);
        $this->assertIsArray($decoded['courses']);
        $this->assertCount(1, $decoded['courses']);
    }

    public function testSaveIncrementalDataTimestampFormat(): void
    {
        $courseData = [['id' => 1, 'title' => '课程']];

        $beforeSave = date('Y-m-d H:i:s');
        sleep(1); // 确保时间差异
        $result = $this->saver->saveIncrementalData($this->tempDir, $courseData);
        $afterSave = date('Y-m-d H:i:s');

        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);

        // 验证时间戳格式
        $this->assertIsString($decoded['since']);
        $this->assertIsString($decoded['backup_time']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $decoded['since']
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $decoded['backup_time']
        );

        // 验证时间戳在合理范围内
        $this->assertGreaterThanOrEqual($beforeSave, $decoded['since']);
        $this->assertLessThanOrEqual($afterSave, $decoded['backup_time']);
    }

    public function testSaveIncrementalDataWithEmptyCourseData(): void
    {
        $courseData = [];

        $result = $this->saver->saveIncrementalData($this->tempDir, $courseData);

        $this->assertFileExists($result);
        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('courses', $decoded);
        $this->assertEmpty($decoded['courses']);
    }

    public function testSaveCourseDataFormatsJsonPretty(): void
    {
        $courseData = [
            [
                'id' => 1,
                'title' => '课程',
                'chapters' => [],
            ],
        ];
        $filename = 'pretty_courses.json';

        $result = $this->saver->saveCourseData($this->tempDir, $courseData, $filename);

        $content = file_get_contents($result);
        $this->assertNotFalse($content);

        // 验证JSON是格式化的（包含换行符和缩进）
        $this->assertStringContainsString("\n", $content);
        $this->assertStringContainsString('    ', $content); // 验证有缩进
    }
}
