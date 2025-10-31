# train-course-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/train-course-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-course-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/train-course-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-course-bundle)
[![License](https://img.shields.io/packagist/l/tourze/train-course-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/your-org/repo/ci.yml?branch=master&style=flat-square)](https://github.com/your-org/repo/actions)
[![Coverage](https://img.shields.io/codecov/c/github/your-org/repo.svg?style=flat-square)](https://codecov.io/gh/your-org/repo)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/train-course-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/train-course-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/train-course-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-course-bundle)

Training course management bundle for safety production training course lifecycle management.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Available Commands](#available-commands)
- [Usage Examples](#usage-examples)
- [Advanced Usage](#advanced-usage)
- [Dependencies](#dependencies)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)

## Features

- Course basic information management (title, description, cover, price, etc.)
- Course category association management
- Course chapter hierarchical structure management
- Multimedia content support (mainly videos)
- Alibaba Cloud VOD video integration and playback
- Course validity period management
- Credit hours statistics and management
- Teacher association management
- Course audit workflow management
- Video playback control (anti-fast-forward, speed limitation, watermark)
- Multi-device playback limitation

## Installation

```bash
composer require tourze/train-course-bundle
```

## Configuration

### Basic Configuration

Configure Alibaba Cloud VOD related environment variables:

```env
JOB_TRAINING_ALIYUN_VOD_ACCESS_KEY_ID=your_access_key_id
JOB_TRAINING_ALIYUN_VOD_ACCESS_KEY_SECRET=your_access_key_secret
```

### Bundle Configuration

Enable the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ...
    Tourze\TrainCourseBundle\TrainCourseBundle::class => ['all' => true],
];
```

## Quick Start

### Creating a Course

```php
<?php

use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Video;

// Create course
$course = new Course();
$course->setTitle('Basic Knowledge of Safety Production');
$course->setDescription('This course introduces basic concepts and regulations of safety production');
$course->setPrice(99.00);
$course->setValidDays(365);
$course->setLearnHours(8);

// Create chapter
$chapter = new Chapter();
$chapter->setCourse($course);
$chapter->setTitle('Chapter 1: Overview of Safety Production');
$chapter->setSort(1);

// Create video
$video = new Video();
$video->setChapter($chapter);
$video->setTitle('1.1 Importance of Safety Production');
$video->setUrl('ali://xxxxx'); // Alibaba Cloud VOD video ID
$video->setDuration(600); // 10 minutes
```

### Getting Video Play URL

```php
<?php

use Tourze\TrainCourseBundle\Service\CourseService;

// Inject service
public function __construct(private CourseService $courseService) {}

// Get video play URL from lesson
$playUrl = $this->courseService->getLessonPlayUrl($lesson);
```

## Available Commands

### train-course:audit

Process course audit tasks with support for automatic approval and timeout detection.

```bash
php bin/console train-course:audit [options]
```

Options:
- `--dry-run` - Dry run mode, only shows what would be processed without execution
- `--auto-approve` - Automatically approve qualified courses (requires configuration)

### course:backup

Backup course data with support for full and incremental backups.

```bash
php bin/console course:backup [options]
```

Options:
- `--output` - Backup file output path (default: var/backup/)
- `--format` - Backup format (json|sql, default: json)
- `--incremental` - Incremental backup mode

### train-course:cleanup

Clean up expired course-related data and cache to maintain system performance.

```bash
php bin/console train-course:cleanup [options]
```

Options:
- `--dry-run` - Dry run mode, only shows what would be cleaned
- `--force` - Force cleanup, skip confirmation prompt
- `--days` - Clean up data older than specified days (default: 30)

### train-course:statistics

Generate course statistics reports including course count, evaluation statistics, 
and collection statistics.

```bash
php bin/console train-course:statistics [options]
```

Options:
- `--format` - Output format (table|json|csv, default: table)
- `--output` - Output file path (optional)
- `--period` - Statistics period (day|week|month|year, default: month)

## Usage Examples

### Course Management

```php
// Get course service
$courseService = $this->get(CourseService::class);

// Find course by ID
$course = $courseService->findById($courseId);

// Check if course is valid
$isValid = $courseService->isCourseValid($course);

// Get course total duration
$totalDuration = $courseService->getCourseTotalDuration($course);

// Get course progress for a user
$progress = $courseService->getCourseProgress($course, $userId);
```

### Video Playback Control

```php
// Set up playback control
$playControl = new CoursePlayControl();
$playControl->setCourse($course);
$playControl->setAllowFastForward(false);
$playControl->setAllowSpeedControl(true);
$playControl->setWatermarkText('Training Course');
$playControl->setMaxDeviceCount(3);

$entityManager->persist($playControl);
$entityManager->flush();
```

## Advanced Usage

### Course Analytics Integration

```php
// Get comprehensive course analytics report
$courseAnalytics = $this->get(CourseAnalyticsService::class);

// Get analytics report for a specific course
$analyticsReport = $courseAnalytics->getCourseAnalyticsReport($course);

// Get course rankings with criteria
$rankings = $courseAnalytics->getCourseRankings([
    'category' => $categoryId,
    'limit' => 10
]);
```

### Course Configuration Management

```php
// Get course configuration service
$configService = $this->get(CourseConfigService::class);

// Get video cache time
$cacheTime = $configService->getVideoPlayUrlCacheTime();

// Check if a feature is enabled
$isEnabled = $configService->isFeatureEnabled('video_watermark');

// Get all configuration
$allConfig = $configService->getAllConfig();
```

## Dependencies

This bundle requires the following Symfony components and third-party packages:

### Core Dependencies

- `symfony/framework-bundle`: ^7.3
- `doctrine/orm`: ^3.0
- `doctrine/doctrine-bundle`: ^2.13
- `nesbot/carbon`: ^2.72 || ^3

### Internal Dependencies

- `tourze/aliyun-vod-bundle`: For Alibaba Cloud VOD integration
- `tourze/doctrine-snowflake-bundle`: For unique ID generation
- `tourze/doctrine-user-bundle`: For user management integration
- `tourze/doctrine-track-bundle`: For entity tracking
- `tourze/train-category-bundle`: For course category management

### Additional Dependencies

- `tourze/enum-extra`: For enhanced enum functionality
- `tourze/doctrine-helper`: For Doctrine utilities

## Testing

Run the test suite:

```bash
# Run all tests
vendor/bin/phpunit packages/train-course-bundle/tests

# Run specific test
vendor/bin/phpunit packages/train-course-bundle/tests/Entity/CourseTest.php

# Run with coverage
vendor/bin/phpunit packages/train-course-bundle/tests --coverage-html coverage
```

## Security

### Security Considerations

1. **Video Access Control**: Always verify user permissions before generating video play URLs
2. **Data Validation**: All entity properties are protected with validation constraints
3. **Input Sanitization**: User input is automatically sanitized through Symfony validation

### Reporting Security Issues

If you discover a security vulnerability, please send an email to security@tourze.com. 
All security vulnerabilities will be promptly addressed.

### Access Control

```php
// Example: Check user access to course
if (!$this->courseService->hasAccess($user, $course)) {
    throw new AccessDeniedException('User does not have access to this course');
}
```

## Important Notes

1. This bundle focuses on course content management and does not include learning 
   management, user management, or evaluation management features
2. Alibaba Cloud VOD functionality requires proper access key configuration
3. It's recommended to use STS temporary credentials instead of fixed AccessKey 
   in production environments
4. For learning management features, please use other dedicated learning management bundles

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## References

- [Alibaba Cloud Video on Demand Documentation](https://help.aliyun.com/product/29932.html)
- [Symfony Doctrine Documentation](https://symfony.com/doc/current/doctrine.html)
- [Symfony Bundle Best Practices](https://symfony.com/doc/current/bundles/best_practices.html)

