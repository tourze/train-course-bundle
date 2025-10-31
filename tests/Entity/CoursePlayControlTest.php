<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;

/**
 * CoursePlayControl 实体单元测试
 *
 * @internal
 */
#[CoversClass(CoursePlayControl::class)]
final class CoursePlayControlTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CoursePlayControl();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'enabled' => ['enabled', true],
            'allowFastForward' => ['allowFastForward', true],
            'allowSpeedControl' => ['allowSpeedControl', true],
            'enableWatermark' => ['enableWatermark', true],
            'maxDeviceCount' => ['maxDeviceCount', 123],
            'playAuthDuration' => ['playAuthDuration', 123],
            'enableResume' => ['enableResume', true],
            'allowSeeking' => ['allowSeeking', true],
            'allowContextMenu' => ['allowContextMenu', true],
            'allowDownload' => ['allowDownload', true],
        ];
    }

    private CoursePlayControl $playControl;

    protected function setUp(): void
    {
        parent::setUp();

        // 单元测试设置
        $this->playControl = new CoursePlayControl();
    }

    public function testGetIdReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getId());
    }

    public function testSetAndGetCreatedByWorksCorrectly(): void
    {
        $createdBy = 'user123';
        $this->playControl->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $this->playControl->getCreatedBy());
    }

    public function testSetAndGetUpdatedByWorksCorrectly(): void
    {
        $updatedBy = 'user456';
        $this->playControl->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $this->playControl->getUpdatedBy());
    }

    public function testSetAndGetCourseWorksCorrectly(): void
    {
        $course = new Course();
        $this->playControl->setCourse($course);

        $this->assertSame($course, $this->playControl->getCourse());
    }

    public function testGetCourseReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getCourse());
    }

    public function testSetAndGetEnabledWorksCorrectly(): void
    {
        $this->playControl->setEnabled(false);

        $this->assertFalse($this->playControl->isEnabled());

        $this->playControl->setEnabled(true);
        $this->assertTrue($this->playControl->isEnabled());
    }

    public function testIsEnabledHasDefaultValue(): void
    {
        $this->assertTrue($this->playControl->isEnabled());
    }

    public function testSetAndGetAllowFastForwardWorksCorrectly(): void
    {
        $this->playControl->setAllowFastForward(true);

        $this->assertTrue($this->playControl->isAllowFastForward());

        $this->playControl->setAllowFastForward(false);
        $this->assertFalse($this->playControl->isAllowFastForward());
    }

    public function testIsAllowFastForwardHasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowFastForward());
    }

    public function testSetAndGetAllowSpeedControlWorksCorrectly(): void
    {
        $this->playControl->setAllowSpeedControl(true);

        $this->assertTrue($this->playControl->isAllowSpeedControl());

        $this->playControl->setAllowSpeedControl(false);
        $this->assertFalse($this->playControl->isAllowSpeedControl());
    }

    public function testIsAllowSpeedControlHasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowSpeedControl());
    }

    public function testSetAndGetAllowedSpeedsWorksCorrectly(): void
    {
        $speeds = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
        $this->playControl->setAllowedSpeeds($speeds);

        $this->assertSame($speeds, $this->playControl->getAllowedSpeeds());
    }

    public function testGetAllowedSpeedsReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getAllowedSpeeds());
    }

    public function testSetAndGetEnableWatermarkWorksCorrectly(): void
    {
        $this->playControl->setEnableWatermark(false);

        $this->assertFalse($this->playControl->isEnableWatermark());

        $this->playControl->setEnableWatermark(true);
        $this->assertTrue($this->playControl->isEnableWatermark());
    }

    public function testIsEnableWatermarkHasDefaultValue(): void
    {
        $this->assertTrue($this->playControl->isEnableWatermark());
    }

    public function testSetAndGetWatermarkTextWorksCorrectly(): void
    {
        $watermarkText = '安全培训专用';
        $this->playControl->setWatermarkText($watermarkText);

        $this->assertSame($watermarkText, $this->playControl->getWatermarkText());
    }

    public function testGetWatermarkTextReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getWatermarkText());
    }

    public function testSetAndGetWatermarkPositionWorksCorrectly(): void
    {
        $position = 'top-left';
        $this->playControl->setWatermarkPosition($position);

        $this->assertSame($position, $this->playControl->getWatermarkPosition());
    }

    public function testGetWatermarkPositionHasDefaultValue(): void
    {
        $this->assertSame('bottom-right', $this->playControl->getWatermarkPosition());
    }

    public function testSetAndGetWatermarkOpacityWorksCorrectly(): void
    {
        $opacity = 80;
        $this->playControl->setWatermarkOpacity($opacity);

        $this->assertSame($opacity, $this->playControl->getWatermarkOpacity());
    }

    public function testGetWatermarkOpacityHasDefaultValue(): void
    {
        $this->assertSame(50, $this->playControl->getWatermarkOpacity());
    }

    public function testSetAndGetMaxDeviceCountWorksCorrectly(): void
    {
        $maxDeviceCount = 5;
        $this->playControl->setMaxDeviceCount($maxDeviceCount);

        $this->assertSame($maxDeviceCount, $this->playControl->getMaxDeviceCount());
    }

    public function testGetMaxDeviceCountHasDefaultValue(): void
    {
        $this->assertSame(3, $this->playControl->getMaxDeviceCount());
    }

    public function testSetAndGetPlayAuthDurationWorksCorrectly(): void
    {
        $duration = 7200; // 2小时
        $this->playControl->setPlayAuthDuration($duration);

        $this->assertSame($duration, $this->playControl->getPlayAuthDuration());
    }

    public function testGetPlayAuthDurationHasDefaultValue(): void
    {
        $this->assertSame(3600, $this->playControl->getPlayAuthDuration());
    }

    public function testSetAndGetEnableResumeWorksCorrectly(): void
    {
        $this->playControl->setEnableResume(false);

        $this->assertFalse($this->playControl->isEnableResume());

        $this->playControl->setEnableResume(true);
        $this->assertTrue($this->playControl->isEnableResume());
    }

    public function testIsEnableResumeHasDefaultValue(): void
    {
        $this->assertTrue($this->playControl->isEnableResume());
    }

    public function testSetAndGetMinWatchDurationWorksCorrectly(): void
    {
        $duration = 1800; // 30分钟
        $this->playControl->setMinWatchDuration($duration);

        $this->assertSame($duration, $this->playControl->getMinWatchDuration());
    }

    public function testGetMinWatchDurationReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getMinWatchDuration());
    }

    public function testSetAndGetProgressCheckIntervalWorksCorrectly(): void
    {
        $interval = 60; // 1分钟
        $this->playControl->setProgressCheckInterval($interval);

        $this->assertSame($interval, $this->playControl->getProgressCheckInterval());
    }

    public function testGetProgressCheckIntervalHasDefaultValue(): void
    {
        $this->assertSame(30, $this->playControl->getProgressCheckInterval());
    }

    public function testSetAndGetAllowSeekingWorksCorrectly(): void
    {
        $this->playControl->setAllowSeeking(true);

        $this->assertTrue($this->playControl->isAllowSeeking());

        $this->playControl->setAllowSeeking(false);
        $this->assertFalse($this->playControl->isAllowSeeking());
    }

    public function testIsAllowSeekingHasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowSeeking());
    }

    public function testSetAndGetAllowContextMenuWorksCorrectly(): void
    {
        $this->playControl->setAllowContextMenu(true);

        $this->assertTrue($this->playControl->isAllowContextMenu());

        $this->playControl->setAllowContextMenu(false);
        $this->assertFalse($this->playControl->isAllowContextMenu());
    }

    public function testIsAllowContextMenuHasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowContextMenu());
    }

    public function testSetAndGetAllowDownloadWorksCorrectly(): void
    {
        $this->playControl->setAllowDownload(true);

        $this->assertTrue($this->playControl->isAllowDownload());

        $this->playControl->setAllowDownload(false);
        $this->assertFalse($this->playControl->isAllowDownload());
    }

    public function testIsAllowDownloadHasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowDownload());
    }

    public function testSetAndGetExtendedConfigWorksCorrectly(): void
    {
        $config = ['feature1' => true, 'feature2' => 'value'];
        $this->playControl->setExtendedConfig($config);

        $this->assertSame($config, $this->playControl->getExtendedConfig());
    }

    public function testGetExtendedConfigReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getExtendedConfig());
    }

    public function testSetAndGetMetadataWorksCorrectly(): void
    {
        $metadata = ['version' => '1.0', 'config' => 'strict'];
        $this->playControl->setMetadata($metadata);

        $this->assertSame($metadata, $this->playControl->getMetadata());
    }

    public function testGetMetadataReturnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getMetadata());
    }

    public function testDefaultValuesAreSetCorrectly(): void
    {
        $this->assertTrue($this->playControl->isEnabled());
        $this->assertFalse($this->playControl->isAllowFastForward());
        $this->assertFalse($this->playControl->isAllowSpeedControl());
        $this->assertTrue($this->playControl->isEnableWatermark());
        $this->assertSame('bottom-right', $this->playControl->getWatermarkPosition());
        $this->assertSame(50, $this->playControl->getWatermarkOpacity());
        $this->assertSame(3, $this->playControl->getMaxDeviceCount());
        $this->assertSame(3600, $this->playControl->getPlayAuthDuration());
        $this->assertTrue($this->playControl->isEnableResume());
        $this->assertSame(30, $this->playControl->getProgressCheckInterval());
        $this->assertFalse($this->playControl->isAllowSeeking());
        $this->assertFalse($this->playControl->isAllowContextMenu());
        $this->assertFalse($this->playControl->isAllowDownload());
    }

    public function testStrictModeConfigurationWorksCorrectly(): void
    {
        // 启用严格模式配置
        $this->playControl->setEnabled(true);
        $this->playControl->setAllowFastForward(false);
        $this->playControl->setAllowSpeedControl(false);
        $this->playControl->setEnableWatermark(true);
        $this->playControl->setMaxDeviceCount(1);
        $this->playControl->setAllowSeeking(false);
        $this->playControl->setAllowContextMenu(false);
        $this->playControl->setAllowDownload(false);

        $this->assertTrue($this->playControl->isEnabled());
        $this->assertFalse($this->playControl->isAllowFastForward());
        $this->assertFalse($this->playControl->isAllowSpeedControl());
        $this->assertTrue($this->playControl->isEnableWatermark());
        $this->assertSame(1, $this->playControl->getMaxDeviceCount());
        $this->assertFalse($this->playControl->isAllowSeeking());
        $this->assertFalse($this->playControl->isAllowContextMenu());
        $this->assertFalse($this->playControl->isAllowDownload());
    }

    public function testWatermarkConfigurationWorksCorrectly(): void
    {
        $this->playControl->setEnableWatermark(true);
        $this->playControl->setWatermarkText('培训专用 - 禁止传播');
        $this->playControl->setWatermarkPosition('center');
        $this->playControl->setWatermarkOpacity(80);

        $this->assertTrue($this->playControl->isEnableWatermark());
        $this->assertSame('培训专用 - 禁止传播', $this->playControl->getWatermarkText());
        $this->assertSame('center', $this->playControl->getWatermarkPosition());
        $this->assertSame(80, $this->playControl->getWatermarkOpacity());
    }

    public function testSpeedControlConfigurationWorksCorrectly(): void
    {
        $this->playControl->setAllowSpeedControl(true);
        $this->playControl->setAllowedSpeeds([0.75, 1.0, 1.25, 1.5]);

        $this->assertTrue($this->playControl->isAllowSpeedControl());
        $this->assertSame([0.75, 1.0, 1.25, 1.5], $this->playControl->getAllowedSpeeds());
    }

    public function testDeviceAndAuthConfigurationWorksCorrectly(): void
    {
        $this->playControl->setMaxDeviceCount(5);
        $this->playControl->setPlayAuthDuration(7200);
        $this->playControl->setEnableResume(true);

        $this->assertSame(5, $this->playControl->getMaxDeviceCount());
        $this->assertSame(7200, $this->playControl->getPlayAuthDuration());
        $this->assertTrue($this->playControl->isEnableResume());
    }

    public function testProgressTrackingConfigurationWorksCorrectly(): void
    {
        $this->playControl->setMinWatchDuration(1800);
        $this->playControl->setProgressCheckInterval(60);

        $this->assertSame(1800, $this->playControl->getMinWatchDuration());
        $this->assertSame(60, $this->playControl->getProgressCheckInterval());
    }

    public function testComplexExtendedConfigWorksCorrectly(): void
    {
        $config = [
            'security' => [
                'prevent_screenshot' => true,
                'prevent_recording' => true,
                'face_detection' => true,
            ],
            'analytics' => [
                'track_mouse_movement' => true,
                'track_focus_events' => true,
                'detailed_progress' => true,
            ],
            'ui' => [
                'hide_controls' => false,
                'custom_theme' => 'dark',
            ],
        ];

        $this->playControl->setExtendedConfig($config);
        $result = $this->playControl->getExtendedConfig();

        $this->assertSame($config, $result);

        // 验证配置结构完整性
        $this->assertArrayHasKey('security', $result);
        $this->assertArrayHasKey('analytics', $result);
        $this->assertArrayHasKey('ui', $result);

        // 验证安全配置设置正确
        $this->assertTrue($result['security']['prevent_screenshot']);
        $this->assertTrue($result['security']['prevent_recording']);
        $this->assertTrue($result['security']['face_detection']);

        // 验证分析配置设置正确
        $this->assertTrue($result['analytics']['track_mouse_movement']);
        $this->assertTrue($result['analytics']['track_focus_events']);
        $this->assertTrue($result['analytics']['detailed_progress']);

        // 验证UI配置有效性
        $this->assertSame('dark', $result['ui']['custom_theme']);
        $this->assertContains($result['ui']['custom_theme'], ['light', 'dark', 'auto']);
    }
}
