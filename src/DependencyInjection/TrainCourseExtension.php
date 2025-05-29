<?php

namespace Tourze\TrainCourseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * 培训课程Bundle扩展类
 * 
 * 负责加载Bundle配置和服务定义
 */
class TrainCourseExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // 处理配置
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        // 将配置设置为容器参数
        $container->setParameter('train_course', $config);
        
        // 加载服务配置
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
        $loader->load('parameters.yaml');
        
        // 只在有Doctrine扩展时加载doctrine.yaml
        if ($container->hasExtension('doctrine')) {
            $loader->load('doctrine.yaml');
        }
    }
    
    public function getAlias(): string
    {
        return 'train_course';
    }
}
