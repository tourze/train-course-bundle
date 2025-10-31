<?php

namespace Tourze\TrainCourseBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * 课程配置服务
 *
 * 统一管理课程相关的配置参数，避免硬编码
 */
#[Autoconfigure(public: true)]
class CourseConfigService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * 获取视频播放地址缓存时间（分钟）
     */
    public function getVideoPlayUrlCacheTime(): int
    {
        try {
            $value = $this->parameterBag->get('train_course.video.play_url_cache_time');

            return is_int($value) ? $value : 30;
        } catch (\InvalidArgumentException $e) {
            return 30;
        }
    }

    /**
     * 获取课程信息缓存时间（分钟）
     */
    public function getCourseInfoCacheTime(): int
    {
        try {
            $value = $this->parameterBag->get('train_course.course.info_cache_time');

            return is_int($value) ? $value : 60;
        } catch (\InvalidArgumentException $e) {
            return 60;
        }
    }

    /**
     * 获取默认课程有效期（天）
     */
    public function getDefaultCourseValidDays(): int
    {
        try {
            $value = $this->parameterBag->get('train_course.course.default_valid_days');

            return is_int($value) ? $value : 365;
        } catch (\InvalidArgumentException $e) {
            return 365;
        }
    }

    /**
     * 获取默认学时
     */
    public function getDefaultLearnHours(): int
    {
        try {
            $value = $this->parameterBag->get('train_course.course.default_learn_hours');

            return is_int($value) ? $value : 8;
        } catch (\InvalidArgumentException $e) {
            return 8;
        }
    }

    /**
     * 获取支持的视频协议列表
     */
    /**
     * @return array<int, string>
     */
    public function getSupportedVideoProtocols(): array
    {
        try {
            $value = $this->parameterBag->get('train_course.video.supported_protocols');

            return is_array($value) ? array_values(array_filter($value, 'is_string')) : [
                'ali://',
                'polyv://',
                'http://',
                'https://',
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'ali://',
                'polyv://',
                'http://',
                'https://',
            ];
        }
    }

    /**
     * 获取 Polyv 视频代理配置
     * @return array<string, string>
     */
    public function getPolyvProxyConfig(): array
    {
        $proxyUrl = $this->get('train_course.polyv.proxy_url', 'http://127.0.0.1:9001/');
        $prefix = $this->get('train_course.polyv.prefix', 'polyv://dp-video/');

        return [
            'proxy_url' => is_string($proxyUrl) ? $proxyUrl : 'http://127.0.0.1:9001/',
            'prefix' => is_string($prefix) ? $prefix : 'polyv://dp-video/',
        ];
    }

    /**
     * 获取课程封面默认配置
     */
    /**
     * @return array<string, mixed>
     */
    public function getCourseCoverConfig(): array
    {
        return [
            'default_cover' => $this->get('train_course.course.default_cover', '/images/default-course-cover.jpg'),
            'max_size' => $this->get('train_course.course.cover_max_size', 2048000), // 2MB
            'allowed_types' => $this->getArrayConfig('train_course.course.cover_allowed_types', ['image/jpeg', 'image/png', 'image/webp']),
        ];
    }

    /**
     * 获取课程播放控制配置
     */
    /**
     * @return array<string, mixed>
     */
    public function getPlayControlConfig(): array
    {
        return [
            'allow_fast_forward' => $this->get('train_course.play_control.allow_fast_forward', false),
            'allow_speed_control' => $this->get('train_course.play_control.allow_speed_control', false),
            'max_device_count' => $this->get('train_course.play_control.max_device_count', 3),
            'enable_watermark' => $this->get('train_course.play_control.enable_watermark', true),
            'play_auth_duration' => $this->get('train_course.play_control.play_auth_duration', 3600), // 1小时
        ];
    }

    /**
     * 获取课程审核配置
     */
    /**
     * @return array<string, mixed>
     */
    public function getAuditConfig(): array
    {
        return [
            'auto_audit' => $this->get('train_course.audit.auto_audit', false),
            'audit_timeout' => $this->get('train_course.audit.timeout', 86400), // 24小时
            'require_manual_review' => $this->get('train_course.audit.require_manual_review', true),
        ];
    }

    /**
     * 检查是否启用了某个功能
     */
    public function isFeatureEnabled(string $feature): bool
    {
        $value = $this->get("train_course.features.{$feature}", false);

        return is_bool($value) ? $value : false;
    }

    /**
     * 获取所有配置
     */
    /**
     * @return array<string, mixed>
     */
    public function getAllConfig(): array
    {
        return [
            'video' => [
                'play_url_cache_time' => $this->getVideoPlayUrlCacheTime(),
                'supported_protocols' => $this->getSupportedVideoProtocols(),
            ],
            'course' => [
                'info_cache_time' => $this->getCourseInfoCacheTime(),
                'default_valid_days' => $this->getDefaultCourseValidDays(),
                'default_learn_hours' => $this->getDefaultLearnHours(),
                'cover' => $this->getCourseCoverConfig(),
            ],
            'polyv' => $this->getPolyvProxyConfig(),
            'play_control' => $this->getPlayControlConfig(),
            'audit' => $this->getAuditConfig(),
        ];
    }

    /**
     * 获取配置值
     *
     * @param string $key     配置键，支持点号分隔的多级键
     * @param mixed  $default 默认值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            return $this->parameterBag->get($key);
        } catch (\InvalidArgumentException $e) {
            return $default;
        }
    }

    /**
     * 获取数组类型配置值
     *
     * @param string               $key     配置键
     * @param array<mixed>         $default 默认值
     * @return array<mixed>
     */
    private function getArrayConfig(string $key, array $default): array
    {
        try {
            $value = $this->parameterBag->get($key);

            return is_array($value) ? $value : $default;
        } catch (\InvalidArgumentException $e) {
            return $default;
        }
    }
}
