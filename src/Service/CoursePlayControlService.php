<?php

namespace Tourze\TrainCourseBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;
use Tourze\TrainCourseBundle\Exception\AuthDataEncodeException;
use Tourze\TrainCourseBundle\Exception\InvalidPlayAuthTokenException;
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
        private readonly CourseConfigService $configService,
    ) {
    }

    /**
     * 获取课程的播放控制配置
     * @return array<string, mixed>
     */
    public function getPlayControlConfig(Course $course): array
    {
        $cacheKey = sprintf('course_play_control_%s', $course->getId());
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            if (is_array($cached)) {
                /** @var array<string, mixed> */
                return $cached;
            }
        }

        $playControl = $this->playControlRepository->findByCourse($course);

        /** @var array<string, mixed> $config */
        $config = null === $playControl
            ? $this->getDefaultPlayControlConfig()
            : $playControl->getPlayControlConfig();

        // 缓存30分钟
        $cacheItem->set($config);
        $cacheItem->expiresAfter(1800);
        $this->cache->save($cacheItem);

        return $config;
    }

    /**
     * 创建或更新课程播放控制配置
     */
    /**
     * @param array<string, mixed> $config
     */
    public function createOrUpdatePlayControl(Course $course, array $config): CoursePlayControl
    {
        $playControl = $this->findOrCreatePlayControl($course);
        $this->applyPlayControlConfig($playControl, $config);
        $this->savePlayControl($playControl);
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

        return (bool) $config['enabled'] && (bool) $config['allow_fast_forward'];
    }

    /**
     * 检查用户是否可以调节播放速度
     */
    public function canControlSpeed(Course $course): bool
    {
        $config = $this->getPlayControlConfig($course);

        return (bool) $config['enabled'] && (bool) $config['allow_speed_control'];
    }

    /**
     * 获取允许的播放速度列表
     * @return array<int, float>
     */
    public function getAllowedSpeeds(Course $course): array
    {
        $config = $this->getPlayControlConfig($course);

        $allowedSpeeds = $config['allowed_speeds'] ?? [1.0];

        if (!is_array($allowedSpeeds)) {
            return [1.0];
        }

        return array_values(array_filter($allowedSpeeds, function ($speed): bool {
            return is_float($speed) || is_int($speed);
        }));
    }

    /**
     * 获取水印配置
     * @return array<string, mixed>
     */
    public function getWatermarkConfig(Course $course): array
    {
        $config = $this->getPlayControlConfig($course);

        $watermark = $config['watermark'] ?? ['enabled' => false];

        if (!is_array($watermark)) {
            /** @var array<string, mixed> */
            return ['enabled' => false];
        }

        /** @var array<string, mixed> */
        return $watermark;
    }

    /**
     * 检查是否启用严格模式
     */
    public function isStrictMode(Course $course): bool
    {
        $config = $this->getPlayControlConfig($course);

        return (bool) $config['enabled']
               && !(bool) $config['allow_fast_forward']
               && !(bool) $config['allow_seeking'];
    }

    /**
     * 生成播放凭证
     * @return array<string, mixed>
     */
    public function generatePlayAuth(Course $course, string $userId): array
    {
        $config = $this->getPlayControlConfig($course);
        $duration = is_int($config['play_auth_duration'] ?? null) ? $config['play_auth_duration'] : 3600;

        $authData = [
            'course_id' => $course->getId(),
            'user_id' => $userId,
            'expires_at' => time() + $duration,
            'max_device_count' => $config['max_device_count'] ?? 3,
            'allow_download' => $config['allow_download'] ?? false,
        ];

        // 这里可以添加JWT或其他加密逻辑
        $encodedData = json_encode($authData);
        if (false === $encodedData) {
            throw new AuthDataEncodeException('Failed to encode auth data');
        }
        $token = base64_encode($encodedData);

        return [
            'token' => $token,
            'expires_at' => $authData['expires_at'],
            'duration' => $duration,
        ];
    }

    /**
     * 验证播放凭证
     * @return array<string, mixed>
     */
    public function validatePlayAuth(string $token): array
    {
        try {
            $decodedToken = base64_decode($token, true);
            if (false === $decodedToken) {
                throw new InvalidPlayAuthTokenException('Invalid base64 token');
            }
            $authData = json_decode($decodedToken, true);

            if (!is_array($authData)) {
                throw new InvalidPlayAuthTokenException('Invalid play auth token format');
            }

            if (!isset($authData['expires_at'])) {
                throw new InvalidPlayAuthTokenException('Invalid play auth token');
            }

            $expiresAt = is_int($authData['expires_at']) ? $authData['expires_at'] : 0;

            if ($expiresAt < time()) {
                throw new InvalidPlayAuthTokenException('Play auth token expired');
            }

            /** @var array<string, mixed> */
            return $authData;
        } catch (\Throwable $e) {
            throw new InvalidPlayAuthTokenException('Invalid play auth token: ' . $e->getMessage());
        }
    }

    /**
     * 获取默认播放控制配置
     */
    /**
     * @return array<string, mixed>
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
    /**
     * @return array<string, mixed>
     */
    public function getPlayControlStatistics(): array
    {
        return $this->playControlRepository->getPlayControlStatistics();
    }

    /**
     * 查找或创建播放控制实体
     */
    private function findOrCreatePlayControl(Course $course): CoursePlayControl
    {
        $playControl = $this->playControlRepository->findByCourse($course);

        if (null === $playControl) {
            $playControl = new CoursePlayControl();
            $playControl->setCourse($course);
        }

        return $playControl;
    }

    /**
     * 应用播放控制配置
     * @param array<string, mixed> $config
     */
    private function applyPlayControlConfig(CoursePlayControl $playControl, array $config): void
    {
        $this->applyBasicControls($playControl, $config);
        $this->applyWatermarkConfig($playControl, $config);
        $this->applyDeviceAndAuthConfig($playControl, $config);
        $this->applyProgressConfig($playControl, $config);
        $this->applyPermissionConfig($playControl, $config);
        $this->applyExtendedConfig($playControl, $config);
    }

    /**
     * 应用基础控制配置
     * @param array<string, mixed> $config
     */
    private function applyBasicControls(CoursePlayControl $playControl, array $config): void
    {
        $playControl->setEnabled($this->getBoolConfig($config, 'enabled', true));
        $playControl->setAllowFastForward($this->getBoolConfig($config, 'allow_fast_forward', false));
        $playControl->setAllowSpeedControl($this->getBoolConfig($config, 'allow_speed_control', false));

        $allowedSpeeds = $this->getArrayConfig($config, 'allowed_speeds', [0.5, 0.75, 1.0, 1.25, 1.5, 2.0]);
        /** @var array<float> $speeds */
        $speeds = array_values(array_filter($allowedSpeeds, function ($item): bool {
            return is_float($item) || is_int($item);
        }));

        $playControl->setAllowedSpeeds($speeds);
    }

    /**
     * 应用水印配置
     * @param array<string, mixed> $config
     */
    private function applyWatermarkConfig(CoursePlayControl $playControl, array $config): void
    {
        $playControl->setEnableWatermark($this->getBoolConfig($config, 'enable_watermark', true));
        $playControl->setWatermarkText($this->getStringOrNullConfig($config, 'watermark_text'));
        $playControl->setWatermarkPosition($this->getStringConfig($config, 'watermark_position', 'bottom-right'));
        $playControl->setWatermarkOpacity($this->getIntConfig($config, 'watermark_opacity', 50));
    }

    /**
     * 应用设备和认证配置
     * @param array<string, mixed> $config
     */
    private function applyDeviceAndAuthConfig(CoursePlayControl $playControl, array $config): void
    {
        $playControl->setMaxDeviceCount($this->getIntConfig($config, 'max_device_count', 3));
        $playControl->setPlayAuthDuration($this->getIntConfig($config, 'play_auth_duration', 3600));
    }

    /**
     * 应用进度配置
     * @param array<string, mixed> $config
     */
    private function applyProgressConfig(CoursePlayControl $playControl, array $config): void
    {
        $playControl->setEnableResume($this->getBoolConfig($config, 'enable_resume', true));
        $playControl->setMinWatchDuration($this->getIntOrNullConfig($config, 'min_watch_duration'));
        $playControl->setProgressCheckInterval($this->getIntConfig($config, 'progress_check_interval', 30));
    }

    /**
     * 应用权限配置
     * @param array<string, mixed> $config
     */
    private function applyPermissionConfig(CoursePlayControl $playControl, array $config): void
    {
        $playControl->setAllowSeeking($this->getBoolConfig($config, 'allow_seeking', false));
        $playControl->setAllowContextMenu($this->getBoolConfig($config, 'allow_context_menu', false));
        $playControl->setAllowDownload($this->getBoolConfig($config, 'allow_download', false));
    }

    /**
     * 应用扩展配置
     * @param array<string, mixed> $config
     */
    private function applyExtendedConfig(CoursePlayControl $playControl, array $config): void
    {
        $extendedConfig = $this->getArrayOrNullConfig($config, 'extended_config');

        if (null !== $extendedConfig) {
            /** @var array<string, mixed> $typedConfig */
            $typedConfig = $extendedConfig;
            $playControl->setExtendedConfig($typedConfig);
        } else {
            $playControl->setExtendedConfig(null);
        }
    }

    /**
     * 保存播放控制实体
     */
    private function savePlayControl(CoursePlayControl $playControl): void
    {
        $this->entityManager->persist($playControl);
        $this->entityManager->flush();
    }

    /**
     * 获取布尔配置值
     * @param array<string, mixed> $config
     */
    private function getBoolConfig(array $config, string $key, bool $default): bool
    {
        $value = $config[$key] ?? $default;

        return is_bool($value) ? $value : $default;
    }

    /**
     * 获取整数配置值
     * @param array<string, mixed> $config
     */
    private function getIntConfig(array $config, string $key, int $default): int
    {
        $value = $config[$key] ?? $default;

        return is_int($value) ? $value : $default;
    }

    /**
     * 获取可空整数配置值
     * @param array<string, mixed> $config
     */
    private function getIntOrNullConfig(array $config, string $key): ?int
    {
        $value = $config[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * 获取字符串配置值
     * @param array<string, mixed> $config
     */
    private function getStringConfig(array $config, string $key, string $default): string
    {
        $value = $config[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * 获取可空字符串配置值
     * @param array<string, mixed> $config
     */
    private function getStringOrNullConfig(array $config, string $key): ?string
    {
        $value = $config[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * 获取数组配置值
     * @param array<string, mixed> $config
     * @param array<mixed> $default
     * @return array<mixed>
     */
    private function getArrayConfig(array $config, string $key, array $default): array
    {
        $value = $config[$key] ?? $default;

        return is_array($value) ? $value : $default;
    }

    /**
     * 获取可空数组配置值
     * @param array<string, mixed> $config
     * @return array<mixed>|null
     */
    private function getArrayOrNullConfig(array $config, string $key): ?array
    {
        $value = $config[$key] ?? null;

        return is_array($value) ? $value : null;
    }
}
