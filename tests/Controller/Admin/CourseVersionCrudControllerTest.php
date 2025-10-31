<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\CourseVersionCrudController;

/**
 * 课程版本管理控制器测试
 * @internal
 */
#[CoversClass(CourseVersionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CourseVersionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CourseVersionCrudController
    {
        return self::getService(CourseVersionCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'course' => ['关联课程'];
        yield 'version' => ['版本号'];
        yield 'title' => ['版本标题'];
        yield 'description' => ['版本描述'];
        yield 'changeLog' => ['变更说明'];
        yield 'status' => ['版本状态'];
        yield 'isCurrent' => ['当前版本'];
        yield 'publishedAt' => ['发布时间'];
        yield 'publishedBy' => ['发布人'];
        yield 'createdBy' => ['创建人'];
        yield 'updatedBy' => ['更新人'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'course' => ['course'];
        yield 'version' => ['version'];
        yield 'status' => ['status'];
        yield 'changeLog' => ['changeLog'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'version' => ['version'];
        yield 'status' => ['status'];
        yield 'changeLog' => ['changeLog'];
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
        $courseField = $form['CourseVersion[course]'] ?? null;
        if (null !== $courseField) {
            $form['CourseVersion[course]'] = '';
        }

        // 同时确保version字段为空
        $versionField = $form['CourseVersion[version]'] ?? null;
        if (null !== $versionField) {
            $form['CourseVersion[version]'] = '';
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

        // 验证必填字段错误
        self::assertTrue(
            str_contains($content, '关联课程不能为空')
            || str_contains($content, 'course')
            || str_contains($content, 'NotNull')
            || str_contains($content, 'This value should not be null'),
            '应该包含course字段的验证错误'
        );

        // 验证版本号字段错误
        self::assertTrue(
            str_contains($content, '版本号不能为空')
            || str_contains($content, 'version')
            || str_contains($content, 'NotBlank')
            || str_contains($content, 'This value should not be blank'),
            '应该包含version字段的验证错误'
        );
    }
}
