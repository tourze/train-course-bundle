# 测试执行指南

## 快速开始

```bash
# 使用优化后的并行测试脚本（推荐）
./packages/train-course-bundle/run-tests.sh

# 执行时间: 约3-4分钟
# 测试数量: 1791个测试用例
```

## 性能对比

| 方法 | 执行时间 | 说明 |
|------|---------|------|
| 原始单进程 | >15分钟 | 不推荐 |
| 并行4进程 | ~5分钟 | 适用于4核CPU |
| 并行8进程 | ~4分钟 | **推荐配置** |

## 其他测试方式

### 1. 手动使用ParaTest

```bash
# 基本用法
vendor/bin/paratest --processes=8 packages/train-course-bundle/tests

# 指定进程数
vendor/bin/paratest --processes=4 packages/train-course-bundle/tests

# 详细输出
vendor/bin/paratest --processes=8 -v packages/train-course-bundle/tests
```

### 2. 测试特定目录

```bash
# 只测试Repository
vendor/bin/paratest --processes=8 packages/train-course-bundle/tests/Repository

# 只测试Entity
vendor/bin/paratest --processes=8 packages/train-course-bundle/tests/Entity

# 只测试Service
vendor/bin/paratest --processes=8 packages/train-course-bundle/tests/Service
```

### 3. 测试单个文件（调试用）

```bash
# 使用标准PHPUnit（单进程，适合调试）
vendor/bin/phpunit packages/train-course-bundle/tests/Repository/CourseRepositoryTest.php

# 使用ParaTest（快速验证）
vendor/bin/paratest packages/train-course-bundle/tests/Repository/CourseRepositoryTest.php
```

### 4. 过滤特定测试

```bash
# 只运行包含"Course"的测试
vendor/bin/paratest --processes=8 --filter=Course packages/train-course-bundle/tests

# 只运行特定方法
vendor/bin/phpunit --filter=testFindValidCourses packages/train-course-bundle/tests
```

## 性能调优

### CPU核心数检测

```bash
# macOS
sysctl -n hw.ncpu

# Linux
nproc

# 使用检测到的核心数
PROCESSES=$(sysctl -n hw.ncpu) ./run-tests.sh
```

### 自定义进程数

```bash
# 保守配置（低配机器）
PROCESSES=2 ./run-tests.sh

# 标准配置
PROCESSES=4 ./run-tests.sh

# 高性能配置（推荐）
PROCESSES=8 ./run-tests.sh

# 最大性能（高端机器）
PROCESSES=16 ./run-tests.sh
```

## 故障排查

### 问题: 数据库连接过多

```bash
# 减少并行进程数
PROCESSES=2 ./run-tests.sh
```

### 问题: 内存不足

```bash
# 增加PHP内存限制
php -d memory_limit=2G vendor/bin/paratest --processes=4
```

### 问题: 测试失败需要调试

```bash
# 使用单进程运行失败的测试
vendor/bin/phpunit packages/train-course-bundle/tests/Repository/CourseRepositoryTest.php -v
```

## 技术说明

### 为什么需要独立进程？

所有测试继承自 `AbstractIntegrationTestCase`，该基类要求使用 `RunTestsInSeparateProcesses` 属性来确保：

- 测试隔离
- 无全局状态污染
- 数据库事务独立
- 防止内存泄漏

### ParaTest如何优化性能？

ParaTest在**文件级别**并行执行测试，而不是方法级别：

1. 将测试文件分配给多个worker进程
2. 每个worker独立运行分配的测试文件
3. 所有worker并行工作，充分利用多核CPU
4. 最后聚合所有结果

**关键优势**:
- 满足 `RunTestsInSeparateProcesses` 要求
- 多核CPU利用率提升3-4倍
- 无需修改测试代码

## 详细报告

查看完整的性能诊断和优化报告：
- [TEST-PERFORMANCE-REPORT.md](./TEST-PERFORMANCE-REPORT.md)

## CI/CD集成

```yaml
# GitHub Actions示例
- name: Run Train Course Bundle Tests
  run: |
    PROCESSES=4 ./packages/train-course-bundle/run-tests.sh
```

```yaml
# GitLab CI示例
test:train-course-bundle:
  script:
    - PROCESSES=4 ./packages/train-course-bundle/run-tests.sh
  parallel: 1
```

## 相关文件

- `run-tests.sh` - 测试执行脚本
- `TEST-PERFORMANCE-REPORT.md` - 性能诊断报告
- `phpunit.xml` - PHPUnit配置（项目根目录）
