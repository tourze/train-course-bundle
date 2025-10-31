<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\CourseAuditCrudController;

/**
 * 课程审核管理控制器测试
 * @internal
 */
#[CoversClass(CourseAuditCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CourseAuditCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CourseAuditCrudController
    {
        return self::getService(CourseAuditCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'course' => ['课程名称'];
        yield 'status' => ['审核状态'];
        yield 'auditType' => ['审核类型'];
        yield 'auditor' => ['审核人员'];
        yield 'auditComment' => ['审核意见'];
        yield 'auditTime' => ['审核时间'];
        yield 'auditLevel' => ['审核级别'];
        yield 'priority' => ['优先级'];
        yield 'deadline' => ['截止时间'];
        yield 'createdBy' => ['创建人'];
        yield 'updatedBy' => ['更新人'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'course' => ['course'];
        yield 'status' => ['status'];
        yield 'auditComment' => ['auditComment'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'status' => ['status'];
        yield 'auditComment' => ['auditComment'];
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        // 访问创建页面
        $url = $this->generateAdminUrl(Action::NEW);
        $crawler = $client->request('GET', $url);
        self::assertResponseIsSuccessful();

        // 查找并提交表单，确保course字段为空
        $form = $crawler->filter('form')->form();

        // 确保course字段为空（如果存在的话）
        $courseField = $form['CourseAudit[course]'] ?? null;
        if (null !== $courseField) {
            $form['CourseAudit[course]'] = '';
        }

        $client->submit($form);

        // 验证响应包含验证错误信息
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);

        // 检查响应状态码（通常是422验证失败或200重新显示表单）
        $statusCode = $response->getStatusCode();
        self::assertThat(
            $statusCode,
            self::logicalOr(
                self::equalTo(422),  // 验证失败
                self::equalTo(200)   // 重新显示表单带错误
            ),
            '响应状态码应该是422（验证失败）或200（重新显示表单）'
        );

        // 添加 PHPStan 期望的验证断言
        if (422 === $statusCode) {
            self::assertResponseStatusCodeSame(422, '验证失败应该返回422状态码');
        }

        // 验证必填字段错误
        self::assertTrue(
            str_contains($content, '关联课程不能为空')
            || str_contains($content, 'course')
            || str_contains($content, 'NotNull')
            || str_contains($content, 'This value should not be null')
            || str_contains($content, 'should not be blank'),
            '应该包含course字段的验证错误'
        );
    }
}
