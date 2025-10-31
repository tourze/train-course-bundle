<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Video;

/**
 * 课程视频数据填充
 * 创建示例视频数据，用于测试阿里云VOD集成和播放功能
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class VideoFixtures extends Fixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    // 视频引用常量 - 用于其他 Fixture 类引用
    public const VIDEO_PHP_INTRO = 'video-php-intro';
    public const VIDEO_PHP_VARIABLES = 'video-php-variables';
    public const VIDEO_PHP_FUNCTIONS = 'video-php-functions';
    public const VIDEO_SYMFONY_ROUTING = 'video-symfony-routing';
    public const VIDEO_SYMFONY_CONTROLLERS = 'video-symfony-controllers';
    public const VIDEO_DOCKER_INSTALL = 'video-docker-install';
    public const VIDEO_DOCKER_COMPOSE = 'video-docker-compose';
    public const VIDEO_SAFETY_INTRO = 'video-safety-intro';

    public function load(ObjectManager $manager): void
    {
        // 创建 PHP 相关教学视频
        $phpIntroVideo = new Video();
        $phpIntroVideo->setTitle('PHP语言介绍');
        $phpIntroVideo->setVideoId('php_intro_001');
        $phpIntroVideo->setSize('52428800'); // 50MB
        $phpIntroVideo->setDuration('780.500'); // 13分钟
        $phpIntroVideo->setCoverUrl('https://images.unsplash.com/photo-1599507593548-1d877d4dceb2?w=800&h=450&fit=crop');
        $phpIntroVideo->setStatus('Normal');
        $manager->persist($phpIntroVideo);
        $this->addReference(self::VIDEO_PHP_INTRO, $phpIntroVideo);

        $phpVariablesVideo = new Video();
        $phpVariablesVideo->setTitle('PHP变量与数据类型');
        $phpVariablesVideo->setVideoId('php_variables_002');
        $phpVariablesVideo->setSize('67108864'); // 64MB
        $phpVariablesVideo->setDuration('960.300'); // 16分钟
        $phpVariablesVideo->setCoverUrl('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=450&fit=crop');
        $phpVariablesVideo->setStatus('Normal');
        $manager->persist($phpVariablesVideo);
        $this->addReference(self::VIDEO_PHP_VARIABLES, $phpVariablesVideo);

        $phpFunctionsVideo = new Video();
        $phpFunctionsVideo->setTitle('PHP函数与面向对象');
        $phpFunctionsVideo->setVideoId('php_functions_003');
        $phpFunctionsVideo->setSize('73400320'); // 70MB
        $phpFunctionsVideo->setDuration('1200.100'); // 20分钟
        $phpFunctionsVideo->setCoverUrl('https://images.unsplash.com/photo-1518773553398-650c184e0bb3?w=800&h=450&fit=crop');
        $phpFunctionsVideo->setStatus('Normal');
        $manager->persist($phpFunctionsVideo);
        $this->addReference(self::VIDEO_PHP_FUNCTIONS, $phpFunctionsVideo);

        // 创建 Symfony 相关教学视频
        $symfonyRoutingVideo = new Video();
        $symfonyRoutingVideo->setTitle('Symfony路由系统');
        $symfonyRoutingVideo->setVideoId('symfony_routing_001');
        $symfonyRoutingVideo->setSize('58720256'); // 56MB
        $symfonyRoutingVideo->setDuration('840.750'); // 14分钟
        $symfonyRoutingVideo->setCoverUrl('https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&h=450&fit=crop');
        $symfonyRoutingVideo->setStatus('Normal');
        $manager->persist($symfonyRoutingVideo);
        $this->addReference(self::VIDEO_SYMFONY_ROUTING, $symfonyRoutingVideo);

        $symfonyControllersVideo = new Video();
        $symfonyControllersVideo->setTitle('Symfony控制器详解');
        $symfonyControllersVideo->setVideoId('symfony_controllers_002');
        $symfonyControllersVideo->setSize('62914560'); // 60MB
        $symfonyControllersVideo->setDuration('1080.200'); // 18分钟
        $symfonyControllersVideo->setCoverUrl('https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&h=450&fit=crop');
        $symfonyControllersVideo->setStatus('Normal');
        $manager->persist($symfonyControllersVideo);
        $this->addReference(self::VIDEO_SYMFONY_CONTROLLERS, $symfonyControllersVideo);

        // 创建 Docker 相关教学视频
        $dockerInstallVideo = new Video();
        $dockerInstallVideo->setTitle('Docker安装与配置');
        $dockerInstallVideo->setVideoId('docker_install_001');
        $dockerInstallVideo->setSize('45088768'); // 43MB
        $dockerInstallVideo->setDuration('720.400'); // 12分钟
        $dockerInstallVideo->setCoverUrl('https://images.unsplash.com/photo-1605745341112-85968b19335b?w=800&h=450&fit=crop');
        $dockerInstallVideo->setStatus('Normal');
        $manager->persist($dockerInstallVideo);
        $this->addReference(self::VIDEO_DOCKER_INSTALL, $dockerInstallVideo);

        $dockerComposeVideo = new Video();
        $dockerComposeVideo->setTitle('Docker Compose实战');
        $dockerComposeVideo->setVideoId('docker_compose_002');
        $dockerComposeVideo->setSize('78643200'); // 75MB
        $dockerComposeVideo->setDuration('1440.600'); // 24分钟
        $dockerComposeVideo->setCoverUrl('https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&h=450&fit=crop');
        $dockerComposeVideo->setStatus('Processing'); // 转码中
        $manager->persist($dockerComposeVideo);
        $this->addReference(self::VIDEO_DOCKER_COMPOSE, $dockerComposeVideo);

        // 创建安全培训相关视频
        $safetyIntroVideo = new Video();
        $safetyIntroVideo->setTitle('企业安全培训导论');
        $safetyIntroVideo->setVideoId('safety_intro_001');
        $safetyIntroVideo->setSize('41943040'); // 40MB
        $safetyIntroVideo->setDuration('600.200'); // 10分钟
        $safetyIntroVideo->setCoverUrl('https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=450&fit=crop');
        $safetyIntroVideo->setStatus('Normal');
        $manager->persist($safetyIntroVideo);
        $this->addReference(self::VIDEO_SAFETY_INTRO, $safetyIntroVideo);

        // 创建一个失败状态的视频（用于测试异常情况）
        $failedVideo = new Video();
        $failedVideo->setTitle('测试失败视频');
        $failedVideo->setVideoId('test_failed_001');
        $failedVideo->setSize('0');
        $failedVideo->setDuration('0.000');
        $failedVideo->setCoverUrl(null);
        $failedVideo->setStatus('Failed');
        $manager->persist($failedVideo);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 100; // 视频是独立的，可以最早创建
    }

    public static function getGroups(): array
    {
        return ['course', 'video', 'test'];
    }
}
