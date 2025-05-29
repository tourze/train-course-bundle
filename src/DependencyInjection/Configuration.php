<?php

namespace Tourze\TrainCourseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * 培训课程Bundle配置类
 * 
 * 定义Bundle的配置结构和默认值
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('train_course');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // 视频相关配置
                ->arrayNode('video')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('play_url_cache_time')
                            ->defaultValue(30)
                            ->info('视频播放地址缓存时间（分钟）')
                        ->end()
                        ->arrayNode('supported_protocols')
                            ->defaultValue(['ali://', 'polyv://', 'http://', 'https://'])
                            ->scalarPrototype()->end()
                            ->info('支持的视频协议')
                        ->end()
                    ->end()
                ->end()
                
                // 课程相关配置
                ->arrayNode('course')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('info_cache_time')
                            ->defaultValue(60)
                            ->info('课程信息缓存时间（分钟）')
                        ->end()
                        ->integerNode('default_valid_days')
                            ->defaultValue(365)
                            ->info('默认课程有效期（天）')
                        ->end()
                        ->integerNode('default_learn_hours')
                            ->defaultValue(8)
                            ->info('默认学时')
                        ->end()
                        ->scalarNode('default_cover')
                            ->defaultValue('/images/default-course-cover.jpg')
                            ->info('默认封面')
                        ->end()
                        ->integerNode('cover_max_size')
                            ->defaultValue(2048000)
                            ->info('封面最大大小（字节）')
                        ->end()
                        ->arrayNode('cover_allowed_types')
                            ->defaultValue(['image/jpeg', 'image/png', 'image/webp'])
                            ->scalarPrototype()->end()
                            ->info('允许的封面类型')
                        ->end()
                    ->end()
                ->end()
                
                // Polyv配置
                ->arrayNode('polyv')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('proxy_url')
                            ->defaultValue('http://127.0.0.1:9001/')
                            ->info('Polyv代理地址')
                        ->end()
                        ->scalarNode('prefix')
                            ->defaultValue('polyv://dp-video/')
                            ->info('Polyv前缀')
                        ->end()
                    ->end()
                ->end()
                
                // 播放控制配置
                ->arrayNode('play_control')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('allow_fast_forward')
                            ->defaultValue(false)
                            ->info('是否允许快进')
                        ->end()
                        ->booleanNode('allow_speed_control')
                            ->defaultValue(false)
                            ->info('是否允许倍速播放')
                        ->end()
                        ->integerNode('max_device_count')
                            ->defaultValue(3)
                            ->info('最大设备数量')
                        ->end()
                        ->booleanNode('enable_watermark')
                            ->defaultValue(true)
                            ->info('是否启用水印')
                        ->end()
                        ->integerNode('play_auth_duration')
                            ->defaultValue(3600)
                            ->info('播放凭证有效期（秒）')
                        ->end()
                    ->end()
                ->end()
                
                // 审核配置
                ->arrayNode('audit')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_audit')
                            ->defaultValue(false)
                            ->info('是否自动审核')
                        ->end()
                        ->integerNode('timeout')
                            ->defaultValue(86400)
                            ->info('审核超时时间（秒）')
                        ->end()
                        ->booleanNode('require_manual_review')
                            ->defaultValue(true)
                            ->info('是否需要人工审核')
                        ->end()
                    ->end()
                ->end()
                
                // 功能开关
                ->arrayNode('features')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('advanced_analytics')
                            ->defaultValue(false)
                            ->info('高级分析功能')
                        ->end()
                        ->booleanNode('ai_content_audit')
                            ->defaultValue(false)
                            ->info('AI内容审核')
                        ->end()
                        ->booleanNode('live_streaming')
                            ->defaultValue(false)
                            ->info('直播功能')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
} 