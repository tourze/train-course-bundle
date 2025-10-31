<?php

namespace Tourze\TrainCourseBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

/**
 * 培训课程Bundle扩展类
 *
 * 负责加载Bundle配置和服务定义
 */
class TrainCourseExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
