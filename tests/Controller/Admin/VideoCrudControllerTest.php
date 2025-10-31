<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\VideoCrudController;

/**
 * 课程视频管理控制器测试
 * @internal
 */
#[CoversClass(VideoCrudController::class)]
#[RunTestsInSeparateProcesses]
final class VideoCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): VideoCrudController
    {
        return self::getService(VideoCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'video_title' => ['视频标题'];
        yield 'video_duration' => ['视频时长'];
        yield 'video_status' => ['视频状态'];
        yield 'create_time' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'videoId' => ['videoId'];
        yield 'size' => ['size'];
        yield 'duration' => ['duration'];
        yield 'coverUrl' => ['coverUrl'];
        yield 'status' => ['status'];
        yield 'vodConfig' => ['vodConfig'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'videoId' => ['videoId'];
        yield 'size' => ['size'];
        yield 'duration' => ['duration'];
        yield 'coverUrl' => ['coverUrl'];
        yield 'status' => ['status'];
        yield 'vodConfig' => ['vodConfig'];
    }
}
