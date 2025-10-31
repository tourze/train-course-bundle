# train-course-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/train-course-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-course-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/train-course-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-course-bundle)
[![License](https://img.shields.io/packagist/l/tourze/train-course-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/your-org/repo/ci.yml?branch=master&style=flat-square)](https://github.com/your-org/repo/actions)
[![Coverage](https://img.shields.io/codecov/c/github/your-org/repo.svg?style=flat-square)](https://codecov.io/gh/your-org/repo)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/train-course-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/train-course-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/train-course-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-course-bundle)

培训课程管理包，负责安全生产培训课程的全生命周期管理。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [可用命令](#可用命令)
- [使用示例](#使用示例)
- [高级用法](#高级用法)
- [依赖项](#依赖项)
- [测试](#测试)
- [安全](#安全)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 课程基础信息管理（标题、描述、封面、价格等）
- 课程分类关联管理
- 课程章节层级结构管理
- 多媒体内容支持（主要是视频）
- 阿里云 VOD 视频集成和播放
- 课程有效期管理
- 学时统计和管理
- 教师关联管理
- 课程审核工作流管理
- 视频播放控制（防快进、倍速限制、水印）
- 多设备播放限制

## 安装

```bash
composer require tourze/train-course-bundle
```

## 配置

### 基础配置

配置阿里云 VOD 相关环境变量：

```env
JOB_TRAINING_ALIYUN_VOD_ACCESS_KEY_ID=your_access_key_id
JOB_TRAINING_ALIYUN_VOD_ACCESS_KEY_SECRET=your_access_key_secret
```

### Bundle 配置

在 Symfony 应用中启用 Bundle：

```php
// config/bundles.php
return [
    // ...
    Tourze\TrainCourseBundle\TrainCourseBundle::class => ['all' => true],
];
```

## 快速开始

### 创建课程

```php
<?php

use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Video;

// 创建课程
$course = new Course();
$course->setTitle('安全生产基础知识');
$course->setDescription('本课程介绍安全生产的基本概念和法规');
$course->setPrice(99.00);
$course->setValidDays(365);
$course->setLearnHours(8);

// 创建章节
$chapter = new Chapter();
$chapter->setCourse($course);
$chapter->setTitle('第一章：安全生产概述');
$chapter->setSort(1);

// 创建视频
$video = new Video();
$video->setChapter($chapter);
$video->setTitle('1.1 安全生产的重要性');
$video->setUrl('ali://xxxxx'); // 阿里云 VOD 视频 ID
$video->setDuration(600); // 10 分钟
```

### 获取视频播放 URL

```php
<?php

use Tourze\TrainCourseBundle\Service\CourseService;

// 注入服务
public function __construct(private CourseService $courseService) {}

// 从课时获取视频播放 URL
$playUrl = $this->courseService->getLessonPlayUrl($lesson);
```

## 可用命令

### train-course:audit

处理课程审核任务，支持自动审核和超时检测。

```bash
php bin/console train-course:audit [options]
```

选项：
- `--dry-run` - 试运行模式，只显示将要处理的内容，不实际执行
- `--auto-approve` - 自动批准符合条件的课程（需要在配置中启用）

### course:backup

备份课程数据，支持全量备份和增量备份。

```bash
php bin/console course:backup [options]
```

选项：
- `--output` - 备份文件输出路径（默认：var/backup/）
- `--format` - 备份格式（json|sql，默认：json）
- `--incremental` - 增量备份模式

### train-course:cleanup

清理课程相关的过期数据和缓存，维护系统性能。

```bash
php bin/console train-course:cleanup [options]
```

选项：
- `--dry-run` - 试运行模式，只显示将要清理的内容
- `--force` - 强制清理，跳过确认提示
- `--days` - 清理多少天前的数据（默认：30）

### train-course:statistics

生成课程统计报告，包括课程数量、评价统计、收藏统计等。

```bash
php bin/console train-course:statistics [options]
```

选项：
- `--format` - 输出格式（table|json|csv，默认：table）
- `--output` - 输出文件路径（可选）
- `--period` - 统计时间段（day|week|month|year，默认：month）

## 使用示例

### 课程管理

```php
// 获取课程服务
$courseService = $this->get(CourseService::class);

// 根据 ID 查找课程
$course = $courseService->findById($courseId);

// 检查课程是否有效
$isValid = $courseService->isCourseValid($course);

// 获取课程总时长
$totalDuration = $courseService->getCourseTotalDuration($course);

// 获取用户的课程进度
$progress = $courseService->getCourseProgress($course, $userId);
```

### 视频播放控制

```php
// 设置播放控制
$playControl = new CoursePlayControl();
$playControl->setCourse($course);
$playControl->setAllowFastForward(false);
$playControl->setAllowSpeedControl(true);
$playControl->setWatermarkText('培训课程');
$playControl->setMaxDeviceCount(3);

$entityManager->persist($playControl);
$entityManager->flush();
```

## 高级用法

### 课程分析集成

```php
// 获取综合课程分析报告
$courseAnalytics = $this->get(CourseAnalyticsService::class);

// 获取特定课程的分析报告
$analyticsReport = $courseAnalytics->getCourseAnalyticsReport($course);

// 根据条件获取课程排名
$rankings = $courseAnalytics->getCourseRankings([
    'category' => $categoryId,
    'limit' => 10
]);
```

### 课程配置管理

```php
// 获取课程配置服务
$configService = $this->get(CourseConfigService::class);

// 获取视频缓存时间
$cacheTime = $configService->getVideoPlayUrlCacheTime();

// 检查功能是否启用
$isEnabled = $configService->isFeatureEnabled('video_watermark');

// 获取所有配置
$allConfig = $configService->getAllConfig();
```

## 依赖项

此 Bundle 需要以下 Symfony 组件和第三方包：

### 核心依赖

- `symfony/framework-bundle`: ^7.3
- `doctrine/orm`: ^3.0
- `doctrine/doctrine-bundle`: ^2.13
- `nesbot/carbon`: ^2.72 || ^3

### 内部依赖

- `tourze/aliyun-vod-bundle`: 用于阿里云 VOD 集成
- `tourze/doctrine-snowflake-bundle`: 用于唯一 ID 生成
- `tourze/doctrine-user-bundle`: 用于用户管理集成
- `tourze/doctrine-track-bundle`: 用于实体跟踪
- `tourze/train-category-bundle`: 用于课程分类管理

### 其他依赖

- `tourze/enum-extra`: 用于增强枚举功能
- `tourze/doctrine-helper`: 用于 Doctrine 工具

## 测试

运行测试套件：

```bash
# 运行所有测试
vendor/bin/phpunit packages/train-course-bundle/tests

# 运行特定测试
vendor/bin/phpunit packages/train-course-bundle/tests/Entity/CourseTest.php

# 运行带覆盖率的测试
vendor/bin/phpunit packages/train-course-bundle/tests --coverage-html coverage
```

## 安全

### 安全考虑

1. **视频访问控制**：在生成视频播放 URL 之前始终验证用户权限
2. **数据验证**：所有实体属性都受到验证约束的保护
3. **输入清理**：用户输入通过 Symfony 验证自动清理

### 报告安全问题

如果您发现安全漏洞，请发送电子邮件至 security@tourze.com。
所有安全漏洞将得到及时处理。

### 访问控制

```php
// 示例：检查用户对课程的访问权限
if (!$this->courseService->hasAccess($user, $course)) {
    throw new AccessDeniedException('用户无权访问此课程');
}
```

## 注意事项

1. 本包专注于课程内容管理，不包含学习管理、用户管理、评价管理等功能
2. 阿里云 VOD 功能需要正确配置访问密钥
3. 建议在生产环境中使用 STS 临时凭证而非固定的 AccessKey
4. 如需学习管理功能，请使用其他专门的学习管理包

## 贡献

请参阅 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 许可证

MIT 许可证 (MIT)。请参阅 [许可证文件](LICENSE) 获取更多信息。

## 参考资料

- [阿里云视频点播文档](https://help.aliyun.com/product/29932.html)
- [Symfony Doctrine 文档](https://symfony.com/doc/current/doctrine.html)
- [Symfony Bundle 最佳实践](https://symfony.com/doc/current/bundles/best_practices.html)

