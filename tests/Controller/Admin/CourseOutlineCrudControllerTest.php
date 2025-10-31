<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TrainCourseBundle\Controller\Admin\CourseOutlineCrudController;

/**
 * 课程大纲管理控制器测试
 * @internal
 */
#[CoversClass(CourseOutlineCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CourseOutlineCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CourseOutlineCrudController
    {
        return self::getService(CourseOutlineCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'course' => ['关联课程'];
        yield 'title' => ['大纲标题'];
        yield 'learningObjectives' => ['学习目标'];
        yield 'contentPoints' => ['内容要点'];
        yield 'keyDifficulties' => ['重点难点'];
        yield 'assessmentCriteria' => ['考核标准'];
        yield 'references' => ['参考资料'];
        yield 'estimatedMinutes' => ['预计学习时长（分钟）'];
        yield 'sortNumber' => ['排序号'];
        yield 'status' => ['状态'];
        yield 'createdBy' => ['创建人'];
        yield 'updatedBy' => ['更新人'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'course' => ['course'];
        yield 'title' => ['title'];
        yield 'learningObjectives' => ['learningObjectives'];
        yield 'contentPoints' => ['contentPoints'];
        yield 'keyDifficulties' => ['keyDifficulties'];
        yield 'assessmentCriteria' => ['assessmentCriteria'];
        yield 'references' => ['references'];
        yield 'estimatedMinutes' => ['estimatedMinutes'];
        yield 'sortNumber' => ['sortNumber'];
        yield 'status' => ['status'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'course' => ['course'];
        yield 'title' => ['title'];
        yield 'learningObjectives' => ['learningObjectives'];
        yield 'contentPoints' => ['contentPoints'];
        yield 'keyDifficulties' => ['keyDifficulties'];
        yield 'assessmentCriteria' => ['assessmentCriteria'];
        yield 'references' => ['references'];
        yield 'estimatedMinutes' => ['estimatedMinutes'];
        yield 'sortNumber' => ['sortNumber'];
        yield 'status' => ['status'];
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
