<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\EvaluateCrudController;

/**
 * 课程评价管理控制器测试
 * @internal
 */
#[CoversClass(EvaluateCrudController::class)]
#[RunTestsInSeparateProcesses]
final class EvaluateCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): EvaluateCrudController
    {
        return self::getService(EvaluateCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'userId' => ['用户ID'];
        yield 'course' => ['评价课程'];
        yield 'rating' => ['评分'];
        yield 'status' => ['评价状态'];
        yield 'createTime' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'userId' => ['userId'];
        yield 'course' => ['course'];
        yield 'rating' => ['rating'];
        yield 'content' => ['content'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'rating' => ['rating'];
        yield 'content' => ['content'];
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
