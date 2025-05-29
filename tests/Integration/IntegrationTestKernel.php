<?php

namespace Tourze\TrainCourseBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Tourze\BundleDependency\ResolveHelper;
use Tourze\TrainCourseBundle\TrainCourseBundle;

/**
 * 集成测试专用内核
 *
 * 用于集成测试的轻量级内核，只加载必要的Bundle和配置
 */
class IntegrationTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        foreach (ResolveHelper::resolveBundleDependencies([TrainCourseBundle::class => ['all' => true]]) as $bundleClass => $bundleConfig) {
            yield new $bundleClass();
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'test' => true,
            'secret' => 'test-secret',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => [
                'log' => true,
            ],
            'validation' => [
                'email_validation_mode' => 'html5',
            ],
            'uid' => [
                'default_uuid_version' => 7,
                'time_based_uuid_version' => 7,
            ],
        ]);

        // 配置Security Bundle
        $container->loadFromExtension('security', [
            'providers' => [
                'test_provider' => [
                    'memory' => [
                        'users' => [
                            'test_user' => [
                                'password' => 'test_password',
                                'roles' => ['ROLE_USER'],
                            ],
                        ],
                    ],
                ],
            ],
            'firewalls' => [
                'main' => [
                    'provider' => 'test_provider',
                    'stateless' => true,
                ],
            ],
            'access_control' => [],
        ]);

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'TrainCourseBundle' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/src/Entity',
                        'prefix' => 'Tourze\\TrainCourseBundle\\Entity',
                        'alias' => 'TrainCourseBundle',
                    ],
                ],
            ],
        ]);

        // 配置train-course-bundle
        $container->loadFromExtension('train_course', [
            'video' => [
                'play_url_cache_time' => 30,
                'supported_protocols' => ['ali://', 'polyv://', 'http://', 'https://'],
            ],
            'course' => [
                'info_cache_time' => 60,
                'default_valid_days' => 365,
                'default_learn_hours' => 8,
                'default_cover' => '/images/default-course-cover.jpg',
                'cover_max_size' => 2048000,
                'cover_allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
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
                'timeout' => 86400,
                'require_manual_review' => true,
            ],
            'features' => [
                'advanced_analytics' => false,
                'ai_content_audit' => false,
                'live_streaming' => false,
            ],
        ]);
        
        // 手动加载服务配置
        $loader->load(__DIR__ . '/../../src/Resources/config/services.yaml');
        $loader->load(__DIR__ . '/../../src/Resources/config/parameters.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // 测试不需要路由配置
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function getProjectDir(): string
    {
        return __DIR__ . '/../..';
    }
}
