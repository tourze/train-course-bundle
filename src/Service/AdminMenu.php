<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\TrainCourseBundle\Controller\Admin\ChapterCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\CollectCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\CourseAuditCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\CourseCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\CourseOutlineCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\CoursePlayControlCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\CourseVersionCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\EvaluateCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\LessonCrudController;
use Tourze\TrainCourseBundle\Controller\Admin\VideoCrudController;

#[MenuProvider]
final class AdminMenu implements MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void
    {
        $trainCourseMenu = $item->addChild('培训课程管理', [
            'icon' => 'fas fa-graduation-cap',
        ]);

        // 课程管理组
        $courseMenu = $trainCourseMenu->addChild('课程管理', [
            'icon' => 'fas fa-book',
        ]);

        $courseMenu->addChild('课程列表', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CourseCrudController::class],
            'extras' => ['icon' => 'fas fa-list'],
        ]);

        $courseMenu->addChild('课程大纲', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CourseOutlineCrudController::class],
            'extras' => ['icon' => 'fas fa-sitemap'],
        ]);

        $courseMenu->addChild('课程版本', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CourseVersionCrudController::class],
            'extras' => ['icon' => 'fas fa-code-branch'],
        ]);

        // 内容管理组
        $contentMenu = $trainCourseMenu->addChild('内容管理', [
            'icon' => 'fas fa-play-circle',
        ]);

        $contentMenu->addChild('课程章节', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => ChapterCrudController::class],
            'extras' => ['icon' => 'fas fa-folder'],
        ]);

        $contentMenu->addChild('课程课时', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => LessonCrudController::class],
            'extras' => ['icon' => 'fas fa-clock'],
        ]);

        $contentMenu->addChild('课程视频', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => VideoCrudController::class],
            'extras' => ['icon' => 'fas fa-video'],
        ]);

        // 播放控制组
        $playControlMenu = $trainCourseMenu->addChild('播放控制', [
            'icon' => 'fas fa-shield-alt',
        ]);

        $playControlMenu->addChild('播放控制', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CoursePlayControlCrudController::class],
            'extras' => ['icon' => 'fas fa-cogs'],
        ]);

        // 互动管理组
        $interactionMenu = $trainCourseMenu->addChild('用户互动', [
            'icon' => 'fas fa-users',
        ]);

        $interactionMenu->addChild('课程收藏', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CollectCrudController::class],
            'extras' => ['icon' => 'fas fa-heart'],
        ]);

        $interactionMenu->addChild('课程评价', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => EvaluateCrudController::class],
            'extras' => ['icon' => 'fas fa-star'],
        ]);

        // 审核管理组
        $auditMenu = $trainCourseMenu->addChild('审核管理', [
            'icon' => 'fas fa-check-circle',
        ]);

        $auditMenu->addChild('课程审核', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CourseAuditCrudController::class],
            'extras' => ['icon' => 'fas fa-gavel'],
        ]);
    }
}
