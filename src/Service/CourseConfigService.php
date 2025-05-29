<?php

namespace Tourze\TrainCourseBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * 课程配置服务
 * 
 * 统一管理课程相关的配置参数，避免硬编码
 */
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
        return $this->parameterBag->get('train_course.video.play_url_cache_time') ?? 30;
    }

    /**
     * 获取课程信息缓存时间（分钟）
     */
    public function getCourseInfoCacheTime(): int
    {
        return $this->parameterBag->get('train_course.course.info_cache_time') ?? 60;
    }

    /**
     * 获取默认课程有效期（天）
     */
    public function getDefaultCourseValidDays(): int
    {
        return $this->parameterBag->get('train_course.course.default_valid_days') ?? 365;
    }

    /**
     * 获取默认学时
     */
    public function getDefaultLearnHours(): int
    {
        return $this->parameterBag->get('train_course.course.default_learn_hours') ?? 8;
    }

    /**
     * 获取支持的视频协议列表
     */
    public function getSupportedVideoProtocols(): array
    {
        return $this->parameterBag->get('train_course.video.supported_protocols') ?? [
            'ali://',
            'polyv://',
            'http://',
            'https://',
        ];
    }

    /**
     * 获取 Polyv 视频代理配置
     */
    public function getPolyvProxyConfig(): array
    {
        return [
            'proxy_url' => $this->parameterBag->get('train_course.polyv.proxy_url') ?? 'http://127.0.0.1:9001/',
            'prefix' => $this->parameterBag->get('train_course.polyv.prefix') ?? 'polyv://dp-video/',
        ];
    }

    /**
     * 获取课程封面默认配置
     */
    public function getCourseCoverConfig(): array
    {
        return [
            'default_cover' => $this->parameterBag->get('train_course.course.default_cover') ?? '/images/default-course-cover.jpg',
            'max_size' => $this->parameterBag->get('train_course.course.cover_max_size') ?? 2048000, // 2MB
            'allowed_types' => $this->parameterBag->get('train_course.course.cover_allowed_types') ?? ['image/jpeg', 'image/png', 'image/webp'],
        ];
    }

    /**
     * 获取课程播放控制配置
     */
    public function getPlayControlConfig(): array
    {
        return [
            'allow_fast_forward' => $this->parameterBag->get('train_course.play_control.allow_fast_forward') ?? false,
            'allow_speed_control' => $this->parameterBag->get('train_course.play_control.allow_speed_control') ?? false,
            'max_device_count' => $this->parameterBag->get('train_course.play_control.max_device_count') ?? 3,
            'enable_watermark' => $this->parameterBag->get('train_course.play_control.enable_watermark') ?? true,
            'play_auth_duration' => $this->parameterBag->get('train_course.play_control.play_auth_duration') ?? 3600, // 1小时
        ];
    }

    /**
     * 获取课程审核配置
     */
    public function getAuditConfig(): array
    {
        return [
            'auto_audit' => $this->parameterBag->get('train_course.audit.auto_audit') ?? false,
            'audit_timeout' => $this->parameterBag->get('train_course.audit.timeout') ?? 86400, // 24小时
            'require_manual_review' => $this->parameterBag->get('train_course.audit.require_manual_review') ?? true,
        ];
    }

    /**
     * 检查是否启用了某个功能
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return $this->parameterBag->get("train_course.features.{$feature}") ?? false;
    }

    /**
     * 获取所有配置
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
} 