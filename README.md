# train-course-bundle

培训课程管理包，负责安全生产培训课程的全生命周期管理。

## 安装

```bash
composer require tourze/train-course-bundle
```

## 配置

需要配置阿里云 VOD 相关环境变量：

```env
JOB_TRAINING_ALIYUN_VOD_ACCESS_KEY_ID=your_access_key_id
JOB_TRAINING_ALIYUN_VOD_ACCESS_KEY_SECRET=your_access_key_secret
```

## 主要功能

- 课程基本信息管理（标题、描述、封面、价格等）
- 课程分类关联管理
- 课程章节层次结构管理
- 多媒体内容支持（主要是视频）
- 阿里云VOD视频集成和播放
- 课程有效期管理
- 学时统计和管理
- 教师关联管理

## 注意事项

1. 本包专注于课程内容管理，不包含学习管理、用户管理、评价管理等功能
2. 阿里云 VOD 功能需要正确配置访问密钥
3. 建议在生产环境中使用 STS 临时凭证而非固定的 AccessKey
4. 如需学习管理功能，请使用其他专门的学习管理包

## 参考文档

- [阿里云视频点播文档](https://help.aliyun.com/product/29932.html)
- [Symfony Doctrine文档](https://symfony.com/doc/current/doctrine.html)
