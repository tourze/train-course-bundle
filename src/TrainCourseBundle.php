<?php

namespace Tourze\TrainCourseBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class TrainCourseBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \WeuiBundle\WeuiBundle::class => ['all' => true],
            \Tourze\AliyunVodBundle\AliyunVodBundle::class => ['all' => true],
        ];
    }
}
