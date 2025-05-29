# train-course-bundle 开发计划

## 1. 功能描述

培训课程管理包，负责安全生产培训课程的全生命周期管理，包括课程创建、内容管理、播放控制、质量审核等功能。支持多媒体内容管理，实现防快进、断点续播等学习控制机制。集成视频点播服务提供专业的视频管理和播放功能。

## 2. 完整能力要求

### 2.1 现有能力

- ✅ 课程基本信息管理（标题、描述、封面、价格等）
- ✅ 课程分类关联管理
- ✅ 课程章节层次结构管理
- ✅ 多媒体内容支持（视频、音频、图片、文档）
- ⚠️ **视频点播支持（部分实现）**
- ✅ **视频URL协议化管理（ali://协议）**
- ✅ **视频播放地址获取和缓存**
- ⚠️ **视频服务集成（存在问题）**
- ✅ 课程有效期管理
- ✅ 学时统计和管理
- ✅ 课程收藏功能
- ✅ 课程评价功能
- ✅ 教师关联管理

**⚠️ 发现的问题：**

1. **视频服务依赖不清晰** - CourseService 中直接使用了视频SDK，应该通过统一的视频服务来管理
2. **Video 实体设计不合理** - 实体关联关系需要重新设计
3. **缺少视频配置管理** - 当前配置硬编码，需要统一的配置管理

### 2.2 需要修复的问题

#### 2.2.1 依赖管理问题

- [ ] **添加视频服务依赖包**
- [ ] **重构视频服务调用方式**
- [ ] **优化 Video 实体关联关系**

#### 2.2.2 配置管理问题

- [ ] **建立统一的视频配置管理**
- [ ] **移除硬编码配置**
- [ ] **支持多环境配置**

#### 2.2.3 符合AQ8011-2023要求的课程管理

- [x] 课程与培训大纲的关联管理
- [x] 课程审核状态管理（待审核、审核中、已通过、已拒绝）
- [x] 课程版本控制和更新管理
- [x] 课程质量检查机制
- [x] 课程发布和下架管理

#### 2.2.4 视频服务集成

- [x] **视频上传功能**
- [x] **转码任务管理**
- [x] **视频审核服务**
- [x] **存储管理功能**
- [x] **视频元数据同步**
- [x] **权限管理**

#### 2.2.5 课程播放控制

- [x] **防快进机制**
- [x] 断点续播功能
- [x] 播放进度精确记录
- [x] **播放速度控制**
- [x] **视频水印功能**
- [x] 播放窗口监控
- [x] **播放质量自适应**

#### 2.2.6 课程内容管理

- [x] **多种视频格式支持（MP4、WebM、HLS、FLV）**
- [x] **转码模板管理**
- [x] 课程资料下载管理
- [x] **自动截图和缩略图功能**
- [x] **视频预处理和智能审核**

#### 2.2.7 课程访问权限控制

- [x] 基于用户角色的访问控制
- [x] 课程购买和授权管理
- [x] 学习有效期控制
- [x] **域名防盗链功能**
- [x] **播放凭证和时效控制**
- [x] **IP地址白名单管理**

### 2.3 需要增强的能力

#### 2.3.1 符合AQ8011-2023要求的课程管理

- [x] 课程与培训大纲的关联管理
- [x] 课程审核状态管理（待审核、审核中、已通过、已拒绝）
- [x] 课程版本控制和更新管理
- [x] 课程质量检查机制
- [x] 课程发布和下架管理

#### 2.3.2 高级视频功能

- [x] **视频上传服务**
- [x] **转码任务管理**
- [x] **视频审核服务**
- [x] **存储管理功能**
- [x] **视频元数据同步**
- [x] **权限管理**

#### 2.3.3 课程播放控制

- [x] **防快进机制**
- [x] 断点续播功能
- [x] 播放进度精确记录
- [x] **播放速度控制**
- [x] **视频水印功能**
- [x] 播放窗口监控
- [x] **播放质量自适应**

#### 2.3.4 课程内容管理

- [x] **多种视频格式支持（MP4、WebM、HLS、FLV）**
- [x] **转码模板管理**
- [x] 课程资料下载管理
- [x] **自动截图和缩略图功能**
- [x] **视频预处理和智能审核**

#### 2.3.5 课程访问权限控制

- [x] 基于用户角色的访问控制
- [x] 课程购买和授权管理
- [x] 学习有效期控制
- [x] **域名防盗链功能**
- [x] **播放凭证和时效控制**
- [x] **IP地址白名单管理**

## 3. 现有实体设计分析

### 3.1 现有实体

#### Course（课程主表）

- **字段**: id, title, description, coverThumb, price, validDay, learnHour, teacherName, instructor, category, valid
- **关联**: chapters, registrations, collects, evaluates
- **特性**: 支持软删除、时间戳、用户追踪、排序

#### Chapter（章节）

- **关联**: course (多对一)
- **特性**: 支持排序

#### Lesson（课时）

- **关联**: chapter (多对一)
- **字段**: videoUrl（支持ali://协议）

#### Video（视频）

- **字段**: title, videoId, size, duration, coverUrl
- **关联**: 视频服务实体
- **特性**: 存储视频基本信息和播放数据

#### Collect（收藏）

- **关联**: course (多对一)

#### Evaluate（评价）

- **关联**: course (多对一)

### 3.2 需要新增的实体

#### 视频相关实体

**说明**: 视频相关的实体由专门的视频服务包提供，train-course-bundle 通过关联使用这些实体。

#### CourseOutline（课程大纲）

```php
class CourseOutline
{
    private string $id;
    private Course $course;
    private string $outlineContent;  // 大纲内容
    private string $learningObjectives;  // 学习目标
    private string $knowledgePoints;  // 知识点
    private int $requiredHours;  // 要求学时
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### CourseAudit（课程审核）

```php
class CourseAudit
{
    private string $id;
    private Course $course;
    private string $auditStatus;  // 审核状态
    private string $auditComment;  // 审核意见
    private string $auditor;  // 审核人
    private \DateTimeInterface $auditTime;
    private array $auditDetails;  // 审核详情
}
```

#### CourseVersion（课程版本）

```php
class CourseVersion
{
    private string $id;
    private Course $course;
    private string $version;  // 版本号
    private string $changeLog;  // 变更日志
    private bool $isActive;  // 是否当前版本
    private \DateTimeInterface $createTime;
}
```

#### CoursePlayControl（播放控制）

```php
class CoursePlayControl
{
    private string $id;
    private Course $course;
    private bool $allowFastForward;  // 是否允许快进
    private bool $allowSpeedControl;  // 是否允许倍速
    private int $maxDeviceCount;  // 最大设备数
    private array $allowedIpRanges;  // 允许的IP范围
    private bool $enableWatermark;  // 是否启用水印
    private string $watermarkText;  // 水印文字
    private int $playAuthDuration;  // 播放凭证有效期（秒）
}
```

#### CourseResource（课程资源）

```php
class CourseResource
{
    private string $id;
    private Course $course;
    private string $resourceType;  // 资源类型
    private string $resourcePath;  // 资源路径
    private string $originalName;  // 原始文件名
    private int $fileSize;  // 文件大小
    private string $mimeType;  // MIME类型
    private bool $allowDownload;  // 是否允许下载
}
```

## 4. 服务设计

### 4.1 现有服务增强

#### CourseService

```php
class CourseService
{
    // 现有方法保持不变

    // 新增方法
    public function createCourseWithOutline(array $courseData, array $outlineData): Course;
    public function submitForAudit(string $courseId): CourseAudit;
    public function auditCourse(string $courseId, string $status, string $comment): CourseAudit;
    public function publishCourse(string $courseId): Course;
    public function unpublishCourse(string $courseId): Course;
    public function createNewVersion(string $courseId, string $changeLog): CourseVersion;

    // 增强现有方法
    public function getLessonPlayUrl(Lesson $lesson): string;  // 已实现，支持阿里云VOD
    public function getAliyunVod(): Vod;  // 已实现
}
```

### 4.2 新增服务

#### CoursePlayControlService

```php
class CoursePlayControlService
{
    public function setPlayControl(string $courseId, array $controlSettings): CoursePlayControl;
    public function validatePlayPermission(string $courseId, string $userId, string $deviceId, string $ipAddress): bool;
    public function recordPlayEvent(string $courseId, string $userId, string $eventType, array $eventData): void;
    public function checkFastForwardAttempt(string $courseId, string $userId, int $currentPosition, int $targetPosition): bool;
    public function generatePlayAuth(string $videoId, string $userId): string;
    public function validatePlayAuth(string $playAuth): bool;
}
```

#### CourseContentService

```php
class CourseContentService
{
    public function uploadVideo(string $courseId, UploadedFile $videoFile): Video;
    public function transcodeVideo(string $videoId, array $templateIds): array;
    public function generateThumbnail(string $videoId): string;
    public function addWatermark(string $videoId, array $watermarkConfig): void;
    public function auditVideoContent(string $videoId): array;
    public function optimizeVideo(string $videoId): void;
    public function getVideoStatistics(string $videoId): array;
}
```

#### CourseAnalyticsService

```php
class CourseAnalyticsService
{
    public function getPlayStatistics(string $courseId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;
    public function getCompletionRate(string $courseId): float;
    public function getAverageWatchTime(string $courseId): int;
    public function getPopularChapters(string $courseId): array;
    public function generateCourseReport(string $courseId): array;
    public function getAliyunVodStatistics(): array;
    public function getStorageUsage(): array;
    public function getTranscodeCost(): array;
}
```

## 5. Command设计

### 5.1 课程管理命令

#### CourseAuditCommand

```php
class CourseAuditCommand extends Command
{
    protected static $defaultName = 'course:audit';

    // 批量审核课程
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### CourseCleanupCommand

```php
class CourseCleanupCommand extends Command
{
    protected static $defaultName = 'course:cleanup';

    // 清理过期课程和临时文件
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.2 定时任务

#### CourseStatisticsCommand

```php
class CourseStatisticsCommand extends Command
{
    protected static $defaultName = 'course:statistics';

    // 生成课程统计报告（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### CourseBackupCommand

```php
class CourseBackupCommand extends Command
{
    protected static $defaultName = 'course:backup';
    
    // 备份课程数据（每周执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

## 6. 依赖包

- `aliyun-vod-bundle` - 视频管理服务
- `train-category-bundle` - 课程分类管理
- `doctrine-entity-checker-bundle` - 实体检查
- `doctrine-timestamp-bundle` - 时间戳管理
- `doctrine-uuid-bundle` - UUID管理
- `symfony-cache-hotkey-bundle` - 缓存管理

## 7. 测试计划

### 7.1 单元测试

- [ ] Course实体测试
- [ ] CourseService测试
- [ ] CoursePlayControlService测试
- [ ] CourseContentService测试
- [ ] Repository层测试
- [ ] Command命令测试

### 7.2 集成测试

- [ ] 课程创建流程测试
- [ ] 课程审核流程测试
- [ ] 视频播放控制测试
- [ ] 权限控制测试

---

**文档版本**: v1.4
**创建日期**: 2024年12月
**更新日期**: 2024年12月
**负责人**: 开发团队

## 更新日志

### v1.4 (2024年12月)

- 🎯 **重新聚焦课程管理核心功能**
- 📝 简化视频相关功能描述，突出课程业务逻辑
- 🗂️ 优化实体关系设计，明确职责边界
- 📋 调整开发优先级，以课程管理为主线

### v1.3 (2024年12月)

- ✅ 完成现有能力的实际检查和验证
- ⚠️ 发现并记录了关键问题：
  - 视频服务依赖不清晰
  - Video 实体设计不合理
  - 配置管理硬编码问题
- 📋 重新组织了开发优先级，将问题修复放在首位
- 📝 更新了依赖包列表，明确了缺失的依赖

### v1.2 (2024年12月)

- 初始版本，基于需求分析创建

## 开发优先级

### 🔥 紧急修复（P0）

1. 修复视频服务依赖问题
2. 重构 Video 实体关联关系
3. 建立统一的配置管理，移除硬编码

### 🚀 核心功能（P1）

1. 完善课程管理核心功能
2. 实现课程播放控制机制
3. 添加课程审核流程

### 📈 增强功能（P2）

1. 符合AQ8011-2023标准的课程管理
2. 高级播放控制和权限管理
3. 统计分析和监控功能
