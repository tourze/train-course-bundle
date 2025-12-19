<?php

namespace Tourze\TrainCourseBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\AliyunVodBundle\AliyunVodBundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\CatalogBundle\CatalogBundle;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTrackBundle\DoctrineTrackBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;
use WeuiBundle\WeuiBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;

class TrainCourseBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            WeuiBundle::class => ['all' => true],
            AliyunVodBundle::class => ['all' => true],
            DoctrineSnowflakeBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
            DoctrineIndexedBundle::class => ['all' => true],
            DoctrineTrackBundle::class => ['all' => true],
            CatalogBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }
}
