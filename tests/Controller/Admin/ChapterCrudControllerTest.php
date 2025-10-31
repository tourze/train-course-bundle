<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\ChapterCrudController;

/**
 * 课程章节管理控制器测试
 * @internal
 */
#[CoversClass(ChapterCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ChapterCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): ChapterCrudController
    {
        return self::getService(ChapterCrudController::class);
    }

    protected function getPreferredDashboardControllerFqcn(): string
    {
        return 'Tourze\SymfonyTestingFramework\Controller\Admin\DashboardController';
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'course' => ['所属课程'];
        yield 'title' => ['章节标题'];
        yield 'sortNumber' => ['排序号'];
        yield 'lessonCount' => ['课时数量'];
        yield 'lessonTime' => ['总学时'];
        yield 'durationSecond' => ['总时长'];
        yield 'createTime' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'course' => ['course'];
        yield 'title' => ['title'];
        yield 'uniqueCode' => ['uniqueCode'];
        yield 'sortNumber' => ['sortNumber'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'course' => ['course'];
        yield 'title' => ['title'];
        yield 'uniqueCode' => ['uniqueCode'];
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
