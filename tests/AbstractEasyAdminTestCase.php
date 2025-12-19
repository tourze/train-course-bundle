<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * Train Course Bundle EasyAdmin 控制器测试基类
 *
 * 处理测试环境的特殊需求，如上传目录创建等
 */
#[CoversClass(AbstractEasyAdminControllerTestCase::class)]
#[RunTestsInSeparateProcesses]
abstract class AbstractEasyAdminTestCase extends AbstractEasyAdminControllerTestCase
{
    protected function afterEasyAdminSetUp(): void
    {
        parent::afterEasyAdminSetUp();

        // 创建必要的上传目录
        $this->createUploadDirectories();
    }

    /**
     * 创建上传目录结构
     * 这些目录被 EasyAdmin 的 ImageField 使用
     */
    private function createUploadDirectories(): void
    {
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $publicDir = $projectDir . '/public';
        $uploadsDir = $publicDir . '/uploads';

        // 创建 public/uploads 目录
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0o777, true);
        }

        // 创建各个具体的上传目录
        $uploadDirs = [
            'avatars',
            'lessons',
            'courses',
            'chapters',
            'videos',
        ];

        foreach ($uploadDirs as $dir) {
            $dirPath = $uploadsDir . '/' . $dir;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0o777, true);
            }
        }
    }
}