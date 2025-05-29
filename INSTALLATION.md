# train-course-bundle 安装和配置指南

## 1. 安装

### 1.1 通过 Composer 安装

```bash
composer require tourze/train-course-bundle
```

### 1.2 注册 Bundle

在 `config/bundles.php` 中添加：

```php
<?php

return [
    // ... 其他 bundles
    Tourze\TrainCourseBundle\TrainCourseBundle::class => ['all' => true],
];
```

## 2. 配置

### 2.1 创建配置文件

创建 `config/packages/train_course.yaml`：

```yaml
train_course:
  # 视频相关配置
  video:
    play_url_cache_time: 30  # 视频播放地址缓存时间（分钟）
    supported_protocols:
      - 'ali://'
      - 'polyv://'
      - 'http://'
      - 'https://'

  # 课程相关配置
  course:
    info_cache_time: 60  # 课程信息缓存时间（分钟）
    default_valid_days: 365  # 默认课程有效期（天）
    default_learn_hours: 8  # 默认学时
    default_cover: '/images/default-course-cover.jpg'  # 默认封面
    cover_max_size: 2048000  # 封面最大大小（字节）
    cover_allowed_types:
      - 'image/jpeg'
      - 'image/png'
      - 'image/webp'

  # 播放控制配置
  play_control:
    allow_fast_forward: false  # 是否允许快进
    allow_speed_control: false  # 是否允许倍速播放
    max_device_count: 3  # 最大设备数量
    enable_watermark: true  # 是否启用水印
    play_auth_duration: 3600  # 播放凭证有效期（秒）

  # 审核配置
  audit:
    auto_audit: false  # 是否自动审核
    timeout: 86400  # 审核超时时间（秒）
    require_manual_review: true  # 是否需要人工审核

  # 功能开关
  features:
    advanced_analytics: false  # 高级分析功能
    ai_content_audit: false  # AI内容审核
    live_streaming: false  # 直播功能
```

### 2.2 配置数据库

确保你的 `config/packages/doctrine.yaml` 包含正确的数据库配置：

```yaml
doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'
    
  orm:
    auto_generate_proxy_classes: true
    naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
    auto_mapping: true
    mappings:
      TrainCourseBundle:
        type: attribute
        dir: '%kernel.project_dir%/vendor/tourze/train-course-bundle/src/Entity'
        prefix: 'Tourze\TrainCourseBundle\Entity'
        alias: TrainCourse
```

### 2.3 创建数据库表

运行数据库迁移命令：

```bash
# 生成迁移文件
php bin/console doctrine:migrations:diff

# 执行迁移
php bin/console doctrine:migrations:migrate
```

或者直接创建表结构：

```bash
php bin/console doctrine:schema:update --force
```

## 3. 依赖配置

### 3.1 配置 aliyun-vod-bundle

train-course-bundle 依赖 aliyun-vod-bundle 来处理视频相关功能。

在 `config/packages/aliyun_vod.yaml` 中配置：

```yaml
aliyun_vod:
  access_key_id: '%env(ALIYUN_ACCESS_KEY_ID)%'
  access_key_secret: '%env(ALIYUN_ACCESS_KEY_SECRET)%'
  region_id: 'cn-shanghai'
  # 其他配置...
```

### 3.2 配置环境变量

在 `.env` 文件中添加：

```env
# 阿里云配置
ALIYUN_ACCESS_KEY_ID=your_access_key_id
ALIYUN_ACCESS_KEY_SECRET=your_access_key_secret

# 数据库配置
DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name?serverVersion=8.0"
```

## 4. 验证安装

### 4.1 检查 Bundle 是否正确加载

```bash
php bin/console debug:container | grep -i course
```

### 4.2 检查命令是否可用

```bash
php bin/console list course
```

应该看到以下命令：
- `course:audit` - 课程审核命令
- `course:backup` - 课程备份命令
- `course:cleanup` - 课程清理命令
- `course:statistics` - 课程统计命令

### 4.3 检查数据库表

```bash
php bin/console doctrine:schema:validate
```

## 5. 基本使用

### 5.1 创建课程

```php
use Tourze\TrainCourseBundle\Service\CourseService;

class CourseController
{
    public function __construct(
        private CourseService $courseService
    ) {}
    
    public function createCourse()
    {
        $courseData = [
            'title' => '安全生产培训课程',
            'description' => '课程描述',
            'price' => 99.00,
            'validDay' => 365,
            'learnHour' => 8,
            'teacherName' => '张老师'
        ];
        
        $course = $this->courseService->createCourse($courseData);
        
        return $course;
    }
}
```

### 5.2 获取课程列表

```php
use Tourze\TrainCourseBundle\Repository\CourseRepository;

class CourseController
{
    public function __construct(
        private CourseRepository $courseRepository
    ) {}
    
    public function getCourses()
    {
        // 获取有效课程
        $courses = $this->courseRepository->findValidCourses();
        
        // 分页获取
        $courses = $this->courseRepository->findPaginated(1, 10);
        
        return $courses;
    }
}
```

### 5.3 播放控制

```php
use Tourze\TrainCourseBundle\Service\CoursePlayControlService;

class PlayController
{
    public function __construct(
        private CoursePlayControlService $playControlService
    ) {}
    
    public function checkPlayPermission(string $courseId, string $userId)
    {
        $deviceId = 'device_123';
        $ipAddress = '192.168.1.100';
        
        $canPlay = $this->playControlService->validatePlayPermission(
            $courseId, 
            $userId, 
            $deviceId, 
            $ipAddress
        );
        
        return $canPlay;
    }
}
```

## 6. 高级配置

### 6.1 自定义缓存配置

```yaml
# config/packages/cache.yaml
framework:
  cache:
    pools:
      train_course.cache:
        adapter: cache.adapter.redis
        default_lifetime: 3600
```

### 6.2 自定义事件监听器

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\TrainCourseBundle\Event\CourseCreatedEvent;

class CourseEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CourseCreatedEvent::class => 'onCourseCreated',
        ];
    }
    
    public function onCourseCreated(CourseCreatedEvent $event): void
    {
        // 处理课程创建事件
        $course = $event->getCourse();
        // 自定义逻辑...
    }
}
```

## 7. 故障排除

### 7.1 常见问题

**问题**: Bundle 无法加载
**解决**: 检查 `config/bundles.php` 中是否正确注册了 Bundle

**问题**: 数据库表未创建
**解决**: 运行 `php bin/console doctrine:schema:update --force`

**问题**: 视频播放功能异常
**解决**: 检查 aliyun-vod-bundle 配置是否正确

### 7.2 调试模式

启用调试模式查看详细错误信息：

```yaml
# config/packages/dev/train_course.yaml
train_course:
  debug: true
```

### 7.3 日志配置

```yaml
# config/packages/monolog.yaml
monolog:
  channels: ['train_course']
  handlers:
    train_course:
      type: rotating_file
      path: '%kernel.logs_dir%/train_course.log'
      level: debug
      channels: ['train_course']
```

## 8. 性能优化

### 8.1 缓存优化

```yaml
train_course:
  course:
    info_cache_time: 120  # 增加缓存时间
  video:
    play_url_cache_time: 60  # 增加视频URL缓存时间
```

### 8.2 数据库优化

```sql
-- 为常用查询字段添加索引
CREATE INDEX idx_course_valid ON course (valid);
CREATE INDEX idx_course_category ON course (category_id);
CREATE INDEX idx_course_create_time ON course (create_time);
```

## 9. 安全配置

### 9.1 播放权限控制

```yaml
train_course:
  play_control:
    max_device_count: 1  # 限制设备数量
    enable_watermark: true  # 启用水印
    play_auth_duration: 1800  # 缩短凭证有效期
```

### 9.2 IP 白名单

```yaml
train_course:
  security:
    allowed_ips:
      - '192.168.1.0/24'
      - '10.0.0.0/8'
```

---

如有问题，请查看 [README.md](README.md) 或提交 Issue。 