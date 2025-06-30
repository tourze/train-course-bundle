<?php

namespace Tourze\TrainCourseBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\TrainCourseBundle\Command\CourseAuditCommand;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * Bundle 集成测试
 *
 * 测试Bundle的加载、服务注册和配置处理
 */
class TrainCourseBundleIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    public function test_bundle_loads_successfully(): void
    {
        self::bootKernel();
        
        $this->assertNotNull(self::$kernel);
    }

    public function test_course_config_service_is_registered(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->assertTrue($container->has(CourseConfigService::class));
        
        $configService = $container->get(CourseConfigService::class);
        $this->assertInstanceOf(CourseConfigService::class, $configService);
    }

    public function test_bundle_services_registration(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        // 测试Bundle基础组件是否正确注册
        
        // 1. CourseConfigService应该可用（它只依赖参数包）
        $this->assertTrue($container->has(\Tourze\TrainCourseBundle\Service\CourseConfigService::class));
        $configService = $container->get(\Tourze\TrainCourseBundle\Service\CourseConfigService::class);
        $this->assertInstanceOf(\Tourze\TrainCourseBundle\Service\CourseConfigService::class, $configService);
        
        // 2. Repository应该注册
        $this->assertTrue($container->has(\Tourze\TrainCourseBundle\Repository\CourseRepository::class));
        
        // 3. Command应该注册
        $this->assertTrue($container->has(\Tourze\TrainCourseBundle\Command\CourseAuditCommand::class));
        
        // Bundle注册测试通过
        $this->assertTrue(true, 'Bundle核心服务注册测试通过');
    }

    public function test_course_repository_is_registered(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->assertTrue($container->has(CourseRepository::class));
        
        $repository = $container->get(CourseRepository::class);
        $this->assertInstanceOf(CourseRepository::class, $repository);
    }

    public function test_course_audit_command_is_registered(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->assertTrue($container->has(CourseAuditCommand::class));
        
        $command = $container->get(CourseAuditCommand::class);
        $this->assertInstanceOf(CourseAuditCommand::class, $command);
    }

    public function test_configuration_is_loaded(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $configService = $container->get(CourseConfigService::class);
        
        // 测试默认配置值
        $this->assertSame(30, $configService->getVideoPlayUrlCacheTime());
        $this->assertSame(60, $configService->getCourseInfoCacheTime());
        $this->assertSame(365, $configService->getDefaultCourseValidDays());
        $this->assertSame(8, $configService->getDefaultLearnHours());
        
        $playControlConfig = $configService->getPlayControlConfig();
        $this->assertFalse($playControlConfig['allow_fast_forward']);
        $this->assertFalse($playControlConfig['allow_speed_control']);
        $this->assertSame(3, $playControlConfig['max_device_count']);
        $this->assertTrue($playControlConfig['enable_watermark']);
        $this->assertSame(3600, $playControlConfig['play_auth_duration']);
        
        $auditConfig = $configService->getAuditConfig();
        $this->assertFalse($auditConfig['auto_audit']);
        $this->assertSame(86400, $auditConfig['audit_timeout']);
        $this->assertTrue($auditConfig['require_manual_review']);
        
        $this->assertFalse($configService->isFeatureEnabled('advanced_analytics'));
        $this->assertFalse($configService->isFeatureEnabled('ai_content_audit'));
        $this->assertFalse($configService->isFeatureEnabled('live_streaming'));
    }

    public function test_supported_video_protocols(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $configService = $container->get(CourseConfigService::class);
        $protocols = $configService->getSupportedVideoProtocols();
        $this->assertContains('ali://', $protocols);
        $this->assertContains('polyv://', $protocols);
        $this->assertContains('http://', $protocols);
        $this->assertContains('https://', $protocols);
    }

    public function test_course_cover_configuration(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $configService = $container->get(CourseConfigService::class);
        $coverConfig = $configService->getCourseCoverConfig();
        
        $this->assertSame('/images/default-course-cover.jpg', $coverConfig['default_cover']);
        $this->assertSame(2048000, $coverConfig['max_size']);
        
        $allowedTypes = $coverConfig['allowed_types'];
        $this->assertContains('image/jpeg', $allowedTypes);
        $this->assertContains('image/png', $allowedTypes);
        $this->assertContains('image/webp', $allowedTypes);
    }
} 