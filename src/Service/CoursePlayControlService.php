<?php

namespace Tourze\TrainCourseBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;
use Tourze\TrainCourseBundle\Repository\CoursePlayControlRepository;

/**
 * 课程播放控制服务
 * 
 * 管理课程的播放控制策略，包括防快进、水印、设备限制等功能
 */
class CoursePlayControlService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CoursePlayControlRepository $playControlRepository,
        private readonly CacheItemPoolInterface $cache,
        private readonly CourseConfigService $configService
    ) {
    }

    /**
     * 获取课程的播放控制配置
     */
    public function getPlayControlConfig(Course $course): array
    {
        $cacheKey = sprintf('course_play_control_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $playControl = $this->playControlRepository->findByCourse($course);
        
        if (!$playControl) {
            // 使用默认配置
            $config = $this->getDefaultPlayControlConfig();
        } else {
            $config = $playControl->getPlayControlConfig();
        }

        // 缓存30分钟
        $cacheItem->set($config);
        $cacheItem->expiresAfter(1800);
        $this->cache->save($cacheItem);

        return $config;
    }

    /**
     * 创建或更新课程播放控制配置
     */
    public function createOrUpdatePlayControl(Course $course, array $config): CoursePlayControl
    {
        $playControl = $this->playControlRepository->findByCourse($course);
        
        if (!$playControl) {
            $playControl = new CoursePlayControl();
            $playControl->setCourse($course);
        }

        // 更新配置
        $playControl->setEnabled($config['enabled'] ?? true);
        $playControl->setAllowFastForward($config['allow_fast_forward'] ?? false);
        $playControl->setAllowSpeedControl($config['allow_speed_control'] ?? false);
        $playControl->setAllowedSpeeds($config['allowed_speeds'] ?? [0.5, 0.75, 1.0, 1.25, 1.5, 2.0]);
        $playControl->setEnableWatermark($config['enable_watermark'] ?? true);
        $playControl->setWatermarkText($config['watermark_text'] ?? null);
        $playControl->setWatermarkPosition($config['watermark_position'] ?? 'bottom-right');
        $playControl->setWatermarkOpacity($config['watermark_opacity'] ?? 50);
        $playControl->setMaxDeviceCount($config['max_device_count'] ?? 3);
        $playControl->setPlayAuthDuration($config['play_auth_duration'] ?? 3600);
        $playControl->setEnableResume($config['enable_resume'] ?? true);
        $playControl->setMinWatchDuration($config['min_watch_duration'] ?? null);
        $playControl->setProgressCheckInterval($config['progress_check_interval'] ?? 30);
        $playControl->setAllowSeeking($config['allow_seeking'] ?? false);
        $playControl->setAllowContextMenu($config['allow_context_menu'] ?? false);
        $playControl->setAllowDownload($config['allow_download'] ?? false);
        $playControl->setExtendedConfig($config['extended_config'] ?? null);

        $this->entityManager->persist($playControl);
        $this->entityManager->flush();

        // 清除缓存
        $this->clearPlayControlCache($course);

        return $playControl;
    }

    /**
     * 启用严格模式（禁用快进和拖拽）
     */
    public function enableStrictMode(Course $course): CoursePlayControl
    {
        $config = [
            'enabled' => true,
            'allow_fast_forward' => false,
            'allow_speed_control' => false,
            'allow_seeking' => false,
            'allow_context_menu' => false,
            'allow_download' => false,
            'enable_watermark' => true,
            'min_watch_duration' => $this->configService->get('course.min_watch_duration', 60),
            'progress_check_interval' => $this->configService->get('course.progress_check_interval', 30),
        ];

        return $this->createOrUpdatePlayControl($course, $config);
    }

    /**
     * 检查用户是否可以快进
     */
    public function canFastForward(Course $course): bool
    {
        $config = $this->getPlayControlConfig($course);
        return $config['enabled'] && $config['allow_fast_forward'];
    }

    /**
     * 检查用户是否可以调节播放速度
     */
    public function canControlSpeed(Course $course): bool
    {
        $config = $this->getPlayControlConfig($course);
        return $config['enabled'] && $config['allow_speed_control'];
    }

    /**
     * 获取允许的播放速度列表
     */
    public function getAllowedSpeeds(Course $course): array
    {
        $config = $this->getPlayControlConfig($course);
        return $config['allowed_speeds'] ?? [1.0];
    }

    /**
     * 获取水印配置
     */
    public function getWatermarkConfig(Course $course): array
    {
        $config = $this->getPlayControlConfig($course);
        return $config['watermark'] ?? ['enabled' => false];
    }

    /**
     * 检查是否启用严格模式
     */
    public function isStrictMode(Course $course): bool
    {
        $config = $this->getPlayControlConfig($course);
        return $config['enabled'] && 
               !$config['allow_fast_forward'] && 
               !$config['allow_seeking'];
    }

    /**
     * 生成播放凭证
     */
    public function generatePlayAuth(Course $course, string $userId): array
    {
        $config = $this->getPlayControlConfig($course);
        $duration = $config['play_auth_duration'] ?? 3600;
        
        $authData = [
            'course_id' => $course->getId(),
            'user_id' => $userId,
            'expires_at' => time() + $duration,
            'max_device_count' => $config['max_device_count'] ?? 3,
            'allow_download' => $config['allow_download'] ?? false,
        ];

        // 这里可以添加JWT或其他加密逻辑
        $token = base64_encode(json_encode($authData));

        return [
            'token' => $token,
            'expires_at' => $authData['expires_at'],
            'duration' => $duration,
        ];
    }

    /**
     * 验证播放凭证
     */
    public function validatePlayAuth(string $token): array
    {
        try {
            $authData = json_decode(base64_decode($token), true);
            
            if (!$authData || !isset($authData['expires_at'])) {
                throw new \InvalidArgumentException('Invalid play auth token');
            }

            if ($authData['expires_at'] < time()) {
                throw new \InvalidArgumentException('Play auth token expired');
            }

            return $authData;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid play auth token: ' . $e->getMessage());
        }
    }

    /**
     * 获取默认播放控制配置
     */
    private function getDefaultPlayControlConfig(): array
    {
        return [
            'enabled' => $this->configService->get('course.play_control_enabled', true),
            'allow_fast_forward' => $this->configService->get('course.allow_fast_forward', false),
            'allow_speed_control' => $this->configService->get('course.allow_speed_control', false),
            'allowed_speeds' => $this->configService->get('course.allowed_speeds', [0.5, 0.75, 1.0, 1.25, 1.5, 2.0]),
            'enable_watermark' => $this->configService->get('course.enable_watermark', true),
            'watermark' => [
                'text' => $this->configService->get('course.watermark_text', '培训课程'),
                'position' => $this->configService->get('course.watermark_position', 'bottom-right'),
                'opacity' => $this->configService->get('course.watermark_opacity', 50),
            ],
            'max_device_count' => $this->configService->get('course.max_device_count', 3),
            'play_auth_duration' => $this->configService->get('course.play_auth_duration', 3600),
            'enable_resume' => $this->configService->get('course.enable_resume', true),
            'min_watch_duration' => $this->configService->get('course.min_watch_duration', null),
            'progress_check_interval' => $this->configService->get('course.progress_check_interval', 30),
            'allow_seeking' => $this->configService->get('course.allow_seeking', false),
            'allow_context_menu' => $this->configService->get('course.allow_context_menu', false),
            'allow_download' => $this->configService->get('course.allow_download', false),
        ];
    }

    /**
     * 清除播放控制缓存
     */
    private function clearPlayControlCache(Course $course): void
    {
        $cacheKey = sprintf('course_play_control_%s', $course->getId());
        $this->cache->deleteItem($cacheKey);
    }

    /**
     * 获取播放控制统计信息
     */
    public function getPlayControlStatistics(): array
    {
        return $this->playControlRepository->getPlayControlStatistics();
    }
} 