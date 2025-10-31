<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\CourseCrudController;

/**
 * 课程管理控制器测试
 * @internal
 */
#[CoversClass(CourseCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CourseCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CourseCrudController
    {
        return self::getService(CourseCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'code' => ['课程编号'];
        yield 'valid' => ['有效状态'];
        yield 'category' => ['课程分类'];
        yield 'title' => ['课程标题'];
        yield 'teacherName' => ['任课老师'];
        yield 'coverThumb' => ['课程封面'];
        yield 'description' => ['描述'];
        yield 'validDay' => ['有效期（天）'];
        yield 'learnHour' => ['毕业学时'];
        yield 'price' => ['课程价格'];
        yield 'createdBy' => ['创建人'];
        yield 'updatedBy' => ['更新人'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'category' => ['category'];
        yield 'title' => ['title'];
        yield 'teacherName' => ['teacherName'];
        yield 'coverThumb' => ['coverThumb'];
        yield 'description' => ['description'];
        yield 'validDay' => ['validDay'];
        yield 'learnHour' => ['learnHour'];
        yield 'price' => ['price'];
        yield 'sortNumber' => ['sortNumber'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'category' => ['category'];
        yield 'title' => ['title'];
        yield 'teacherName' => ['teacherName'];
        yield 'coverThumb' => ['coverThumb'];
        yield 'description' => ['description'];
        yield 'validDay' => ['validDay'];
        yield 'learnHour' => ['learnHour'];
        yield 'price' => ['price'];
        yield 'sortNumber' => ['sortNumber'];
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client); // 确保设置静态客户端
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
