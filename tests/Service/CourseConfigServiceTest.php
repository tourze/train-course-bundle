<?php

namespace Tourze\TrainCourseBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * CourseConfigService 单元测试
 */
class CourseConfigServiceTest extends TestCase
{
    private ParameterBagInterface $parameterBag;
    private CourseConfigService $service;

    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->service = new CourseConfigService($this->parameterBag);
    }

    public function test_getVideoPlayUrlCacheTime_withConfiguredValue_returnsConfiguredValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.video.play_url_cache_time')
            ->willReturn(45);

        $result = $this->service->getVideoPlayUrlCacheTime();
        $this->assertSame(45, $result);
    }

    public function test_getVideoPlayUrlCacheTime_withoutConfiguredValue_returnsDefaultValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.video.play_url_cache_time')
            ->willReturn(null);

        $result = $this->service->getVideoPlayUrlCacheTime();
        $this->assertSame(30, $result);
    }

    public function test_getCourseInfoCacheTime_withConfiguredValue_returnsConfiguredValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.course.info_cache_time')
            ->willReturn(120);

        $result = $this->service->getCourseInfoCacheTime();
        $this->assertSame(120, $result);
    }

    public function test_getCourseInfoCacheTime_withoutConfiguredValue_returnsDefaultValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.course.info_cache_time')
            ->willReturn(null);

        $result = $this->service->getCourseInfoCacheTime();
        $this->assertSame(60, $result);
    }

    public function test_getDefaultCourseValidDays_withConfiguredValue_returnsConfiguredValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.course.default_valid_days')
            ->willReturn(180);

        $result = $this->service->getDefaultCourseValidDays();
        $this->assertSame(180, $result);
    }

    public function test_getDefaultCourseValidDays_withoutConfiguredValue_returnsDefaultValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.course.default_valid_days')
            ->willReturn(null);

        $result = $this->service->getDefaultCourseValidDays();
        $this->assertSame(365, $result);
    }

    public function test_getDefaultLearnHours_withConfiguredValue_returnsConfiguredValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.course.default_learn_hours')
            ->willReturn(16);

        $result = $this->service->getDefaultLearnHours();
        $this->assertSame(16, $result);
    }

    public function test_getDefaultLearnHours_withoutConfiguredValue_returnsDefaultValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.course.default_learn_hours')
            ->willReturn(null);

        $result = $this->service->getDefaultLearnHours();
        $this->assertSame(8, $result);
    }

    public function test_getSupportedVideoProtocols_withConfiguredValue_returnsConfiguredValue(): void
    {
        $protocols = ['ali://', 'tencent://', 'http://'];
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.video.supported_protocols')
            ->willReturn($protocols);

        $result = $this->service->getSupportedVideoProtocols();
        $this->assertSame($protocols, $result);
    }

    public function test_getSupportedVideoProtocols_withoutConfiguredValue_returnsDefaultValue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.video.supported_protocols')
            ->willReturn(null);

        $result = $this->service->getSupportedVideoProtocols();
        $expected = ['ali://', 'polyv://', 'http://', 'https://'];
        $this->assertSame($expected, $result);
    }

    public function test_getPolyvProxyConfig_withConfiguredValues_returnsConfiguredValues(): void
    {
        $this->parameterBag->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['train_course.polyv.proxy_url', 'http://custom-proxy:8080/'],
                ['train_course.polyv.prefix', 'custom://prefix/'],
            ]);

        $result = $this->service->getPolyvProxyConfig();
        $expected = [
            'proxy_url' => 'http://custom-proxy:8080/',
            'prefix' => 'custom://prefix/',
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getPolyvProxyConfig_withoutConfiguredValues_returnsDefaultValues(): void
    {
        $this->parameterBag->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['train_course.polyv.proxy_url', null],
                ['train_course.polyv.prefix', null],
            ]);

        $result = $this->service->getPolyvProxyConfig();
        $expected = [
            'proxy_url' => 'http://127.0.0.1:9001/',
            'prefix' => 'polyv://dp-video/',
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getCourseCoverConfig_withConfiguredValues_returnsConfiguredValues(): void
    {
        $this->parameterBag->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['train_course.course.default_cover', '/custom/cover.jpg'],
                ['train_course.course.cover_max_size', 5242880], // 5MB
                ['train_course.course.cover_allowed_types', ['image/jpeg', 'image/png']],
            ]);

        $result = $this->service->getCourseCoverConfig();
        $expected = [
            'default_cover' => '/custom/cover.jpg',
            'max_size' => 5242880,
            'allowed_types' => ['image/jpeg', 'image/png'],
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getCourseCoverConfig_withoutConfiguredValues_returnsDefaultValues(): void
    {
        $this->parameterBag->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['train_course.course.default_cover', null],
                ['train_course.course.cover_max_size', null],
                ['train_course.course.cover_allowed_types', null],
            ]);

        $result = $this->service->getCourseCoverConfig();
        $expected = [
            'default_cover' => '/images/default-course-cover.jpg',
            'max_size' => 2048000,
            'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getPlayControlConfig_withConfiguredValues_returnsConfiguredValues(): void
    {
        $this->parameterBag->expects($this->exactly(5))
            ->method('get')
            ->willReturnMap([
                ['train_course.play_control.allow_fast_forward', true],
                ['train_course.play_control.allow_speed_control', true],
                ['train_course.play_control.max_device_count', 5],
                ['train_course.play_control.enable_watermark', false],
                ['train_course.play_control.play_auth_duration', 7200], // 2小时
            ]);

        $result = $this->service->getPlayControlConfig();
        $expected = [
            'allow_fast_forward' => true,
            'allow_speed_control' => true,
            'max_device_count' => 5,
            'enable_watermark' => false,
            'play_auth_duration' => 7200,
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getPlayControlConfig_withoutConfiguredValues_returnsDefaultValues(): void
    {
        $this->parameterBag->expects($this->exactly(5))
            ->method('get')
            ->willReturnMap([
                ['train_course.play_control.allow_fast_forward', null],
                ['train_course.play_control.allow_speed_control', null],
                ['train_course.play_control.max_device_count', null],
                ['train_course.play_control.enable_watermark', null],
                ['train_course.play_control.play_auth_duration', null],
            ]);

        $result = $this->service->getPlayControlConfig();
        $expected = [
            'allow_fast_forward' => false,
            'allow_speed_control' => false,
            'max_device_count' => 3,
            'enable_watermark' => true,
            'play_auth_duration' => 3600,
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getAuditConfig_withConfiguredValues_returnsConfiguredValues(): void
    {
        $this->parameterBag->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['train_course.audit.auto_audit', true],
                ['train_course.audit.timeout', 172800], // 48小时
                ['train_course.audit.require_manual_review', false],
            ]);

        $result = $this->service->getAuditConfig();
        $expected = [
            'auto_audit' => true,
            'audit_timeout' => 172800,
            'require_manual_review' => false,
        ];
        $this->assertSame($expected, $result);
    }

    public function test_getAuditConfig_withoutConfiguredValues_returnsDefaultValues(): void
    {
        $this->parameterBag->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['train_course.audit.auto_audit', null],
                ['train_course.audit.timeout', null],
                ['train_course.audit.require_manual_review', null],
            ]);

        $result = $this->service->getAuditConfig();
        $expected = [
            'auto_audit' => false,
            'audit_timeout' => 86400,
            'require_manual_review' => true,
        ];
        $this->assertSame($expected, $result);
    }

    public function test_isFeatureEnabled_withEnabledFeature_returnsTrue(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.features.advanced_analytics')
            ->willReturn(true);

        $result = $this->service->isFeatureEnabled('advanced_analytics');
        $this->assertTrue($result);
    }

    public function test_isFeatureEnabled_withDisabledFeature_returnsFalse(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.features.advanced_analytics')
            ->willReturn(false);

        $result = $this->service->isFeatureEnabled('advanced_analytics');
        $this->assertFalse($result);
    }

    public function test_isFeatureEnabled_withUnconfiguredFeature_returnsFalse(): void
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->with('train_course.features.unknown_feature')
            ->willReturn(null);

        $result = $this->service->isFeatureEnabled('unknown_feature');
        $this->assertFalse($result);
    }

    public function test_getAllConfig_returnsCompleteConfigStructure(): void
    {
        // 模拟所有配置参数 - getAllConfig 会调用 18 次 get 方法
        $this->parameterBag->expects($this->exactly(18))
            ->method('get')
            ->willReturnMap([
                // Video config
                ['train_course.video.play_url_cache_time', 30],
                ['train_course.video.supported_protocols', ['ali://', 'polyv://', 'http://', 'https://']],
                
                // Course config
                ['train_course.course.info_cache_time', 60],
                ['train_course.course.default_valid_days', 365],
                ['train_course.course.default_learn_hours', 8],
                ['train_course.course.default_cover', '/images/default-course-cover.jpg'],
                ['train_course.course.cover_max_size', 2048000],
                ['train_course.course.cover_allowed_types', ['image/jpeg', 'image/png', 'image/webp']],
                
                // Polyv config
                ['train_course.polyv.proxy_url', 'http://127.0.0.1:9001/'],
                ['train_course.polyv.prefix', 'polyv://dp-video/'],
                
                // Play control config
                ['train_course.play_control.allow_fast_forward', false],
                ['train_course.play_control.allow_speed_control', false],
                ['train_course.play_control.max_device_count', 3],
                ['train_course.play_control.enable_watermark', true],
                ['train_course.play_control.play_auth_duration', 3600],
                
                // Audit config
                ['train_course.audit.auto_audit', false],
                ['train_course.audit.timeout', 86400],
                ['train_course.audit.require_manual_review', true],
            ]);

        $result = $this->service->getAllConfig();

        $expected = [
            'video' => [
                'play_url_cache_time' => 30,
                'supported_protocols' => ['ali://', 'polyv://', 'http://', 'https://'],
            ],
            'course' => [
                'info_cache_time' => 60,
                'default_valid_days' => 365,
                'default_learn_hours' => 8,
                'cover' => [
                    'default_cover' => '/images/default-course-cover.jpg',
                    'max_size' => 2048000,
                    'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
                ],
            ],
            'polyv' => [
                'proxy_url' => 'http://127.0.0.1:9001/',
                'prefix' => 'polyv://dp-video/',
            ],
            'play_control' => [
                'allow_fast_forward' => false,
                'allow_speed_control' => false,
                'max_device_count' => 3,
                'enable_watermark' => true,
                'play_auth_duration' => 3600,
            ],
            'audit' => [
                'auto_audit' => false,
                'audit_timeout' => 86400,
                'require_manual_review' => true,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function test_getAllConfig_withMixedConfiguredAndDefaultValues_returnsCorrectStructure(): void
    {
        // 模拟部分配置有值，部分使用默认值 - getAllConfig 会调用 18 次 get 方法
        $this->parameterBag->expects($this->exactly(18))
            ->method('get')
            ->willReturnMap([
                // Video config - 自定义值
                ['train_course.video.play_url_cache_time', 45],
                ['train_course.video.supported_protocols', null], // 使用默认值
                
                // Course config - 混合
                ['train_course.course.info_cache_time', null], // 使用默认值
                ['train_course.course.default_valid_days', 180], // 自定义值
                ['train_course.course.default_learn_hours', null], // 使用默认值
                ['train_course.course.default_cover', '/custom/cover.jpg'], // 自定义值
                ['train_course.course.cover_max_size', null], // 使用默认值
                ['train_course.course.cover_allowed_types', null], // 使用默认值
                
                // Polyv config - 使用默认值
                ['train_course.polyv.proxy_url', null],
                ['train_course.polyv.prefix', null],
                
                // Play control config - 自定义值
                ['train_course.play_control.allow_fast_forward', true],
                ['train_course.play_control.allow_speed_control', true],
                ['train_course.play_control.max_device_count', 5],
                ['train_course.play_control.enable_watermark', false],
                ['train_course.play_control.play_auth_duration', 7200],
                
                // Audit config - 使用默认值
                ['train_course.audit.auto_audit', null],
                ['train_course.audit.timeout', null],
                ['train_course.audit.require_manual_review', null],
            ]);

        $result = $this->service->getAllConfig();

        $expected = [
            'video' => [
                'play_url_cache_time' => 45, // 自定义值
                'supported_protocols' => ['ali://', 'polyv://', 'http://', 'https://'], // 默认值
            ],
            'course' => [
                'info_cache_time' => 60, // 默认值
                'default_valid_days' => 180, // 自定义值
                'default_learn_hours' => 8, // 默认值
                'cover' => [
                    'default_cover' => '/custom/cover.jpg', // 自定义值
                    'max_size' => 2048000, // 默认值
                    'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'], // 默认值
                ],
            ],
            'polyv' => [
                'proxy_url' => 'http://127.0.0.1:9001/', // 默认值
                'prefix' => 'polyv://dp-video/', // 默认值
            ],
            'play_control' => [
                'allow_fast_forward' => true, // 自定义值
                'allow_speed_control' => true, // 自定义值
                'max_device_count' => 5, // 自定义值
                'enable_watermark' => false, // 自定义值
                'play_auth_duration' => 7200, // 自定义值
            ],
            'audit' => [
                'auto_audit' => false, // 默认值
                'audit_timeout' => 86400, // 默认值
                'require_manual_review' => true, // 默认值
            ],
        ];

        $this->assertSame($expected, $result);
    }
}
