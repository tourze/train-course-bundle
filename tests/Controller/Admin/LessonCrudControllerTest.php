<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\LessonCrudController;

/**
 * 课时管理控制器测试
 * @internal
 */
#[CoversClass(LessonCrudController::class)]
#[RunTestsInSeparateProcesses]
final class LessonCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): LessonCrudController
    {
        return self::getService(LessonCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'chapter' => ['所属章节'];
        yield 'title' => ['课时名称'];
        yield 'durationSecond' => ['视频时长(秒)'];
        yield 'sortNumber' => ['排序号'];
        yield 'lessonTime' => ['学时'];
        yield 'createTime' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'chapter' => ['chapter'];
        yield 'title' => ['title'];
        yield 'videoUrl' => ['videoUrl'];
        yield 'durationSecond' => ['durationSecond'];
        yield 'sortNumber' => ['sortNumber'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'chapter' => ['chapter'];
        yield 'title' => ['title'];
        yield 'videoUrl' => ['videoUrl'];
        yield 'durationSecond' => ['durationSecond'];
        yield 'sortNumber' => ['sortNumber'];
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        self::assertResponseIsSuccessful();

        // 提交空表单触发验证
        $form = $crawler->selectButton('Create')->form();
        $client->submit($form);

        // 验证必填字段的错误提示
        self::assertResponseStatusCodeSame(422);
    }
}
