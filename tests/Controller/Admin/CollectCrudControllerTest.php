<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\CollectCrudController;

/**
 * 课程收藏管理控制器测试
 * @internal
 */
#[CoversClass(CollectCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CollectCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CollectCrudController
    {
        return self::getService(CollectCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'userId' => ['用户ID'];
        yield 'course' => ['收藏课程'];
        yield 'status' => ['收藏状态'];
        yield 'createTime' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        // Collect 一般不需要手动创建，由系统自动生成
        // 但为了测试框架需要，我们提供一个基本的字段测试
        yield 'userId' => ['userId'];
        yield 'course' => ['course'];
        yield 'status' => ['status'];
    }

    public static function provideEditPageFields(): iterable
    {
        // Collect 一般不需要编辑
        // 但为了测试框架需要，我们提供一个基本的字段测试
        yield 'status' => ['status'];
        yield 'collectGroup' => ['collectGroup'];
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
