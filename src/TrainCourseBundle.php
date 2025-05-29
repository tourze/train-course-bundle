<?php

namespace Tourze\TrainCourseBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class TrainCourseBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
            \Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
            \WeuiBundle\WeuiBundle::class => ['all' => true],
            \Tourze\AliyunVodBundle\AliyunVodBundle::class => ['all' => true],
            \Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle::class => ['all' => true],
            \Tourze\DoctrineUserBundle\DoctrineUserBundle::class => ['all' => true],
            \Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
            \Tourze\DoctrineTrackBundle\DoctrineTrackBundle::class => ['all' => true],
        ];
    }
}
