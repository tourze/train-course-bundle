<?php

namespace Tourze\TrainCourseBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;

/**
 * CoursePlayControl 实体单元测试
 */
class CoursePlayControlTest extends TestCase
{
    private CoursePlayControl $playControl;

    protected function setUp(): void
    {
        $this->playControl = new CoursePlayControl();
    }

    public function test_getId_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getId());
    }

    public function test_setAndGetCreatedBy_worksCorrectly(): void
    {
        $createdBy = 'user123';
        $result = $this->playControl->setCreatedBy($createdBy);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($createdBy, $this->playControl->getCreatedBy());
    }

    public function test_setAndGetUpdatedBy_worksCorrectly(): void
    {
        $updatedBy = 'user456';
        $result = $this->playControl->setUpdatedBy($updatedBy);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($updatedBy, $this->playControl->getUpdatedBy());
    }

    public function test_setAndGetCourse_worksCorrectly(): void
    {
        $course = new Course();
        $result = $this->playControl->setCourse($course);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($course, $this->playControl->getCourse());
    }

    public function test_getCourse_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getCourse());
    }

    public function test_setAndGetEnabled_worksCorrectly(): void
    {
        $result = $this->playControl->setEnabled(false);
        
        $this->assertSame($this->playControl, $result);
        $this->assertFalse($this->playControl->isEnabled());
        
        $this->playControl->setEnabled(true);
        $this->assertTrue($this->playControl->isEnabled());
    }

    public function test_isEnabled_hasDefaultValue(): void
    {
        $this->assertTrue($this->playControl->isEnabled());
    }

    public function test_setAndGetAllowFastForward_worksCorrectly(): void
    {
        $result = $this->playControl->setAllowFastForward(true);
        
        $this->assertSame($this->playControl, $result);
        $this->assertTrue($this->playControl->isAllowFastForward());
        
        $this->playControl->setAllowFastForward(false);
        $this->assertFalse($this->playControl->isAllowFastForward());
    }

    public function test_isAllowFastForward_hasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowFastForward());
    }

    public function test_setAndGetAllowSpeedControl_worksCorrectly(): void
    {
        $result = $this->playControl->setAllowSpeedControl(true);
        
        $this->assertSame($this->playControl, $result);
        $this->assertTrue($this->playControl->isAllowSpeedControl());
        
        $this->playControl->setAllowSpeedControl(false);
        $this->assertFalse($this->playControl->isAllowSpeedControl());
    }

    public function test_isAllowSpeedControl_hasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowSpeedControl());
    }

    public function test_setAndGetAllowedSpeeds_worksCorrectly(): void
    {
        $speeds = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
        $result = $this->playControl->setAllowedSpeeds($speeds);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($speeds, $this->playControl->getAllowedSpeeds());
    }

    public function test_getAllowedSpeeds_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getAllowedSpeeds());
    }

    public function test_setAndGetEnableWatermark_worksCorrectly(): void
    {
        $result = $this->playControl->setEnableWatermark(false);
        
        $this->assertSame($this->playControl, $result);
        $this->assertFalse($this->playControl->isEnableWatermark());
        
        $this->playControl->setEnableWatermark(true);
        $this->assertTrue($this->playControl->isEnableWatermark());
    }

    public function test_isEnableWatermark_hasDefaultValue(): void
    {
        $this->assertTrue($this->playControl->isEnableWatermark());
    }

    public function test_setAndGetWatermarkText_worksCorrectly(): void
    {
        $watermarkText = '安全培训专用';
        $result = $this->playControl->setWatermarkText($watermarkText);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($watermarkText, $this->playControl->getWatermarkText());
    }

    public function test_getWatermarkText_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getWatermarkText());
    }

    public function test_setAndGetWatermarkPosition_worksCorrectly(): void
    {
        $position = 'top-left';
        $result = $this->playControl->setWatermarkPosition($position);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($position, $this->playControl->getWatermarkPosition());
    }

    public function test_getWatermarkPosition_hasDefaultValue(): void
    {
        $this->assertSame('bottom-right', $this->playControl->getWatermarkPosition());
    }

    public function test_setAndGetWatermarkOpacity_worksCorrectly(): void
    {
        $opacity = 80;
        $result = $this->playControl->setWatermarkOpacity($opacity);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($opacity, $this->playControl->getWatermarkOpacity());
    }

    public function test_getWatermarkOpacity_hasDefaultValue(): void
    {
        $this->assertSame(50, $this->playControl->getWatermarkOpacity());
    }

    public function test_setAndGetMaxDeviceCount_worksCorrectly(): void
    {
        $maxDeviceCount = 5;
        $result = $this->playControl->setMaxDeviceCount($maxDeviceCount);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($maxDeviceCount, $this->playControl->getMaxDeviceCount());
    }

    public function test_getMaxDeviceCount_hasDefaultValue(): void
    {
        $this->assertSame(3, $this->playControl->getMaxDeviceCount());
    }

    public function test_setAndGetPlayAuthDuration_worksCorrectly(): void
    {
        $duration = 7200; // 2小时
        $result = $this->playControl->setPlayAuthDuration($duration);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($duration, $this->playControl->getPlayAuthDuration());
    }

    public function test_getPlayAuthDuration_hasDefaultValue(): void
    {
        $this->assertSame(3600, $this->playControl->getPlayAuthDuration());
    }

    public function test_setAndGetEnableResume_worksCorrectly(): void
    {
        $result = $this->playControl->setEnableResume(false);
        
        $this->assertSame($this->playControl, $result);
        $this->assertFalse($this->playControl->isEnableResume());
        
        $this->playControl->setEnableResume(true);
        $this->assertTrue($this->playControl->isEnableResume());
    }

    public function test_isEnableResume_hasDefaultValue(): void
    {
        $this->assertTrue($this->playControl->isEnableResume());
    }

    public function test_setAndGetMinWatchDuration_worksCorrectly(): void
    {
        $duration = 1800; // 30分钟
        $result = $this->playControl->setMinWatchDuration($duration);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($duration, $this->playControl->getMinWatchDuration());
    }

    public function test_getMinWatchDuration_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getMinWatchDuration());
    }

    public function test_setAndGetProgressCheckInterval_worksCorrectly(): void
    {
        $interval = 60; // 1分钟
        $result = $this->playControl->setProgressCheckInterval($interval);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($interval, $this->playControl->getProgressCheckInterval());
    }

    public function test_getProgressCheckInterval_hasDefaultValue(): void
    {
        $this->assertSame(30, $this->playControl->getProgressCheckInterval());
    }

    public function test_setAndGetAllowSeeking_worksCorrectly(): void
    {
        $result = $this->playControl->setAllowSeeking(true);
        
        $this->assertSame($this->playControl, $result);
        $this->assertTrue($this->playControl->isAllowSeeking());
        
        $this->playControl->setAllowSeeking(false);
        $this->assertFalse($this->playControl->isAllowSeeking());
    }

    public function test_isAllowSeeking_hasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowSeeking());
    }

    public function test_setAndGetAllowContextMenu_worksCorrectly(): void
    {
        $result = $this->playControl->setAllowContextMenu(true);
        
        $this->assertSame($this->playControl, $result);
        $this->assertTrue($this->playControl->isAllowContextMenu());
        
        $this->playControl->setAllowContextMenu(false);
        $this->assertFalse($this->playControl->isAllowContextMenu());
    }

    public function test_isAllowContextMenu_hasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowContextMenu());
    }

    public function test_setAndGetAllowDownload_worksCorrectly(): void
    {
        $result = $this->playControl->setAllowDownload(true);
        
        $this->assertSame($this->playControl, $result);
        $this->assertTrue($this->playControl->isAllowDownload());
        
        $this->playControl->setAllowDownload(false);
        $this->assertFalse($this->playControl->isAllowDownload());
    }

    public function test_isAllowDownload_hasDefaultValue(): void
    {
        $this->assertFalse($this->playControl->isAllowDownload());
    }

    public function test_setAndGetExtendedConfig_worksCorrectly(): void
    {
        $config = ['feature1' => true, 'feature2' => 'value'];
        $result = $this->playControl->setExtendedConfig($config);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($config, $this->playControl->getExtendedConfig());
    }

    public function test_getExtendedConfig_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getExtendedConfig());
    }

    public function test_setAndGetMetadata_worksCorrectly(): void
    {
        $metadata = ['version' => '1.0', 'config' => 'strict'];
        $result = $this->playControl->setMetadata($metadata);
        
        $this->assertSame($this->playControl, $result);
        $this->assertSame($metadata, $this->playControl->getMetadata());
    }

    public function test_getMetadata_returnsNullByDefault(): void
    {
        $this->assertNull($this->playControl->getMetadata());
    }

    public function test_defaultValues_areSetCorrectly(): void
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

    public function test_strictModeConfiguration_worksCorrectly(): void
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

    public function test_watermarkConfiguration_worksCorrectly(): void
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

    public function test_speedControlConfiguration_worksCorrectly(): void
    {
        $this->playControl->setAllowSpeedControl(true);
        $this->playControl->setAllowedSpeeds([0.75, 1.0, 1.25, 1.5]);
        
        $this->assertTrue($this->playControl->isAllowSpeedControl());
        $this->assertSame([0.75, 1.0, 1.25, 1.5], $this->playControl->getAllowedSpeeds());
    }

    public function test_deviceAndAuthConfiguration_worksCorrectly(): void
    {
        $this->playControl->setMaxDeviceCount(5);
        $this->playControl->setPlayAuthDuration(7200);
        $this->playControl->setEnableResume(true);
        
        $this->assertSame(5, $this->playControl->getMaxDeviceCount());
        $this->assertSame(7200, $this->playControl->getPlayAuthDuration());
        $this->assertTrue($this->playControl->isEnableResume());
    }

    public function test_progressTrackingConfiguration_worksCorrectly(): void
    {
        $this->playControl->setMinWatchDuration(1800);
        $this->playControl->setProgressCheckInterval(60);
        
        $this->assertSame(1800, $this->playControl->getMinWatchDuration());
        $this->assertSame(60, $this->playControl->getProgressCheckInterval());
    }

    public function test_complexExtendedConfig_worksCorrectly(): void
    {
        $config = [
            'security' => [
                'prevent_screenshot' => true,
                'prevent_recording' => true,
                'face_detection' => true
            ],
            'analytics' => [
                'track_mouse_movement' => true,
                'track_focus_events' => true,
                'detailed_progress' => true
            ],
            'ui' => [
                'hide_controls' => false,
                'custom_theme' => 'dark'
            ]
        ];
        
        $this->playControl->setExtendedConfig($config);
        $result = $this->playControl->getExtendedConfig();
        
        $this->assertSame($config, $result);
        $this->assertTrue($result['security']['prevent_screenshot']);
        $this->assertTrue($result['analytics']['track_mouse_movement']);
        $this->assertSame('dark', $result['ui']['custom_theme']);
    }
} 