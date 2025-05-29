# Changelog

## [Unreleased] - 2024-12-XX

### Removed

- 删除了 `Classroom` 实体及其相关逻辑
- 删除了 `Registration` 实体及其相关逻辑
- 删除了 `Student` 实体及其相关逻辑
- 删除了 `Paper` 实体及其相关逻辑
- 删除了 `LearnSession` 实体及其相关逻辑
- 删除了 `LearnLog` 实体及其相关逻辑
- 删除了 `Collect` 实体及其相关逻辑
- 删除了 `Evaluate` 实体及其相关逻辑
- 移除了所有相关的 Repository 类
- 移除了所有相关的 Procedure 类
- 移除了 `CourseService` 中的学习管理相关方法

### Changed

- 大幅简化了课程管理，专注于课程核心功能
- 重构了实体关系，移除了学习管理、用户管理、评价管理等功能
- 简化了 `CourseService`，只保留课程和视频相关功能
- 更新了 README 文档，移除了非核心功能说明

### Reason

- 根据用户需求，只关注课程部分，删除学习管理、用户管理等功能
- 大幅简化系统架构，减少不必要的复杂性
- 让 train-course-bundle 专注于课程内容管理和阿里云VOD集成

## [1.0.0] - 2024-12-XX

### Added

- 初始版本发布
- 完整的课程管理功能
- 阿里云 VOD 集成
- 课程章节管理
- 课程评价和收藏功能
