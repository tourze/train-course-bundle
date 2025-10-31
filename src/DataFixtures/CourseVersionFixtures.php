<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseVersion;

/**
 * 课程版本数据填充
 * 创建课程的版本控制记录，包括历史版本、当前版本和版本变更记录
 */
#[When(env: 'disabled')]
#[When(env: 'dev')]
class CourseVersionFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 版本引用常量 - 用于其他 Fixture 类引用
    public const VERSION_PHP_V1_0 = 'version-php-v1-0';
    public const VERSION_PHP_V1_1 = 'version-php-v1-1';
    public const VERSION_PHP_V2_0_CURRENT = 'version-php-v2-0-current';
    public const VERSION_SYMFONY_V1_0 = 'version-symfony-v1-0';
    public const VERSION_DOCKER_DRAFT = 'version-docker-draft';

    public function load(ObjectManager $manager): void
    {
        // 获取课程引用
        $phpCourse = $this->getCourseReference('php-course');
        $symfonyAdvanced = $this->getCourseReference('symfony-advanced');
        $dockerCourse = $this->getCourseReference('docker-course');
        $safetyCourse = $this->getCourseReference('safety-course');
        $managementCourse = $this->getCourseReference('management-course');

        // 创建 PHP 课程的历史版本 1.0（已归档）
        $phpV10 = new CourseVersion();
        $phpV10->setCourse($phpCourse);
        $phpV10->setVersion('1.0.0');
        $phpV10->setTitle('PHP基础入门 v1.0');
        $phpV10->setDescription('PHP课程的初始版本，包含基础语法和简单应用');
        $phpV10->setChangeLog(implode("\n", [
            '版本 1.0.0 发布说明：',
            '• 初始版本发布',
            '• 包含PHP基础语法',
            '• 基本的函数和数组操作',
            '• 简单的面向对象概念',
        ]));
        $phpV10->setStatus('archived');
        $phpV10->setIsCurrent(false);
        $phpV10->setCourseSnapshot([
            'title' => 'PHP基础入门',
            'validDay' => 180,
            'learnHour' => 20,
            'teacherName' => '张老师',
            'description' => '这是PHP课程的第一个版本',
            'price' => '15.00',
        ]);
        $phpV10->setChaptersSnapshot([
            [
                'id' => 1,
                'title' => 'PHP简介',
                'sortNumber' => 100,
            ],
            [
                'id' => 2,
                'title' => 'PHP基础语法',
                'sortNumber' => 200,
            ],
        ]);
        $phpV10->setLessonsSnapshot([
            [
                'id' => 1,
                'chapter_id' => 1,
                'title' => 'PHP是什么',
                'durationSecond' => 600,
            ],
            [
                'id' => 2,
                'chapter_id' => 1,
                'title' => 'PHP环境搭建',
                'durationSecond' => 900,
            ],
        ]);
        $phpV10->setPublishedAt(new \DateTimeImmutable('-6 months'));
        $phpV10->setPublishedBy('course_admin_001');
        $phpV10->setMetadata([
            'creation_notes' => '初始版本，基础内容覆盖',
            'student_feedback_score' => 7.2,
            'completion_rate' => 68.5,
            'total_students' => 1250,
        ]);
        $manager->persist($phpV10);
        $this->addReference(self::VERSION_PHP_V1_0, $phpV10);

        // 创建 PHP 课程的版本 1.1（已归档）
        $phpV11 = new CourseVersion();
        $phpV11->setCourse($phpCourse);
        $phpV11->setVersion('1.1.0');
        $phpV11->setTitle('PHP基础入门 v1.1');
        $phpV11->setDescription('在v1.0基础上增加了实践项目和进阶内容');
        $phpV11->setChangeLog(implode("\n", [
            '版本 1.1.0 更新说明：',
            '• 新增实践项目章节',
            '• 优化面向对象编程内容',
            '• 增加数据库操作基础',
            '• 修复了部分课程内容的错误',
            '• 更新了开发环境配置说明',
        ]));
        $phpV11->setStatus('archived');
        $phpV11->setIsCurrent(false);
        $phpV11->setCourseSnapshot([
            'title' => 'PHP基础入门',
            'validDay' => 365,
            'learnHour' => 30,
            'teacherName' => '张老师',
            'description' => 'PHP基础课程，增加了实践内容',
            'price' => '18.00',
        ]);
        $phpV11->setChaptersSnapshot([
            [
                'id' => 1,
                'title' => 'PHP简介',
                'sortNumber' => 100,
            ],
            [
                'id' => 2,
                'title' => 'PHP基础语法',
                'sortNumber' => 200,
            ],
            [
                'id' => 3,
                'title' => '面向对象编程',
                'sortNumber' => 300,
            ],
            [
                'id' => 4,
                'title' => '实践项目',
                'sortNumber' => 400,
            ],
        ]);
        $phpV11->setLessonsSnapshot([
            [
                'id' => 1,
                'chapter_id' => 1,
                'title' => 'PHP是什么',
                'durationSecond' => 720,
            ],
            [
                'id' => 2,
                'chapter_id' => 1,
                'title' => 'PHP环境搭建',
                'durationSecond' => 1080,
            ],
            [
                'id' => 3,
                'chapter_id' => 4,
                'title' => '构建简单的博客系统',
                'durationSecond' => 1800,
            ],
        ]);
        $phpV11->setPublishedAt(new \DateTimeImmutable('-3 months'));
        $phpV11->setPublishedBy('course_admin_001');
        $phpV11->setMetadata([
            'update_reason' => '基于学员反馈增加实践内容',
            'student_feedback_score' => 8.4,
            'completion_rate' => 75.2,
            'total_students' => 2100,
            'major_improvements' => ['practical_projects', 'oop_content', 'database_basics'],
        ]);
        $manager->persist($phpV11);
        $this->addReference(self::VERSION_PHP_V1_1, $phpV11);

        // 创建 PHP 课程的当前版本 2.0
        $phpV20Current = new CourseVersion();
        $phpV20Current->setCourse($phpCourse);
        $phpV20Current->setVersion('2.0.0');
        $phpV20Current->setTitle('PHP基础入门 v2.0');
        $phpV20Current->setDescription('全面重构的PHP基础课程，采用PHP 8.0+特性，现代化开发实践');
        $phpV20Current->setChangeLog(implode("\n", [
            '版本 2.0.0 重大更新：',
            '• 全面升级到PHP 8.0+语法',
            '• 新增类型声明和属性类型',
            '• 引入Composer包管理',
            '• 重新设计课程结构',
            '• 增加单元测试章节',
            '• 现代化开发工具和最佳实践',
            '• 完全重写所有代码示例',
        ]));
        $phpV20Current->setStatus('published');
        $phpV20Current->setIsCurrent(true); // 当前版本
        $phpV20Current->setCourseSnapshot([
            'title' => 'PHP基础入门',
            'validDay' => 365,
            'learnHour' => 40,
            'teacherName' => '测试讲师',
            'description' => '这是一个用于DataFixtures测试的示例课程',
            'price' => '20.00',
        ]);
        $phpV20Current->setChaptersSnapshot([
            [
                'id' => 1,
                'title' => 'PHP语言基础',
                'sortNumber' => 100,
            ],
            [
                'id' => 2,
                'title' => 'PHP高级特性',
                'sortNumber' => 200,
            ],
        ]);
        $phpV20Current->setLessonsSnapshot([
            [
                'id' => 1,
                'chapter_id' => 1,
                'title' => 'PHP 8.0新特性',
                'durationSecond' => 900,
            ],
            [
                'id' => 2,
                'chapter_id' => 1,
                'title' => '类型系统详解',
                'durationSecond' => 1200,
            ],
            [
                'id' => 3,
                'chapter_id' => 2,
                'title' => 'Composer包管理',
                'durationSecond' => 1500,
            ],
        ]);
        $phpV20Current->setPublishedAt(new \DateTimeImmutable('-1 week'));
        $phpV20Current->setPublishedBy('course_admin_002');
        $phpV20Current->setMetadata([
            'major_release' => true,
            'breaking_changes' => true,
            'php_version_requirement' => '8.0+',
            'modernization_complete' => true,
            'expected_feedback_score' => 9.0,
            'target_completion_rate' => 85.0,
        ]);
        $manager->persist($phpV20Current);
        $this->addReference(self::VERSION_PHP_V2_0_CURRENT, $phpV20Current);

        // 创建 Symfony 课程版本 1.0
        $symfonyV10 = new CourseVersion();
        $symfonyV10->setCourse($symfonyAdvanced);
        $symfonyV10->setVersion('1.0.0');
        $symfonyV10->setTitle('Symfony高级开发 v1.0');
        $symfonyV10->setDescription('Symfony框架的高级应用开发课程');
        $symfonyV10->setChangeLog(implode("\n", [
            '版本 1.0.0 发布说明：',
            '• Symfony 6.4 LTS版本',
            '• 依赖注入深入讲解',
            '• 事件系统和中间件',
            '• 高级路由和安全',
            '• 性能优化实践',
        ]));
        $symfonyV10->setStatus('published');
        $symfonyV10->setIsCurrent(true);
        $symfonyV10->setCourseSnapshot([
            'title' => 'Symfony高级开发',
            'validDay' => 365,
            'learnHour' => 40,
            'teacherName' => '测试讲师',
            'description' => '这是一个用于DataFixtures测试的示例课程',
            'price' => '20.00',
        ]);
        $symfonyV10->setChaptersSnapshot([
            [
                'id' => 1,
                'title' => 'Symfony框架介绍',
                'sortNumber' => 100,
            ],
            [
                'id' => 2,
                'title' => 'Symfony实战开发',
                'sortNumber' => 200,
            ],
        ]);
        $symfonyV10->setLessonsSnapshot([
            [
                'id' => 1,
                'chapter_id' => 1,
                'title' => '框架架构解析',
                'durationSecond' => 1800,
            ],
            [
                'id' => 2,
                'chapter_id' => 2,
                'title' => 'API开发实战',
                'durationSecond' => 2400,
            ],
        ]);
        $symfonyV10->setPublishedAt(new \DateTimeImmutable('-2 weeks'));
        $symfonyV10->setPublishedBy('symfony_expert_001');
        $symfonyV10->setMetadata([
            'framework_version' => 'Symfony 6.4',
            'target_audience' => 'advanced_developers',
            'prerequisites_verified' => true,
            'expert_reviewed' => true,
        ]);
        $manager->persist($symfonyV10);
        $this->addReference(self::VERSION_SYMFONY_V1_0, $symfonyV10);

        // 创建 Docker 课程的草稿版本
        $dockerDraft = new CourseVersion();
        $dockerDraft->setCourse($dockerCourse);
        $dockerDraft->setVersion('0.9.0-beta');
        $dockerDraft->setTitle('Docker容器化部署 v0.9 Beta');
        $dockerDraft->setDescription('Docker课程的测试版本，正在完善中');
        $dockerDraft->setChangeLog(implode("\n", [
            '版本 0.9.0-beta 测试版：',
            '• 基础内容已完成',
            '• 正在添加Kubernetes内容',
            '• 待完善生产环境部署章节',
            '• 需要补充更多实例',
        ]));
        $dockerDraft->setStatus('draft');
        $dockerDraft->setIsCurrent(false);
        $dockerDraft->setCourseSnapshot([
            'title' => 'Docker容器化部署',
            'validDay' => 365,
            'learnHour' => 40,
            'teacherName' => '测试讲师',
            'description' => '这是一个用于DataFixtures测试的示例课程',
            'price' => '20.00',
        ]);
        $dockerDraft->setChaptersSnapshot([
            [
                'id' => 1,
                'title' => 'Docker容器基础',
                'sortNumber' => 100,
            ],
        ]);
        $dockerDraft->setLessonsSnapshot([
            [
                'id' => 1,
                'chapter_id' => 1,
                'title' => 'Docker入门',
                'durationSecond' => 1200,
            ],
        ]);
        $dockerDraft->setPublishedAt(null); // 草稿未发布
        $dockerDraft->setPublishedBy(null);
        $dockerDraft->setMetadata([
            'draft_stage' => 'content_development',
            'completion_percentage' => 65,
            'review_required' => true,
            'estimated_completion' => (new \DateTime('+2 weeks'))->format('Y-m-d'),
        ]);
        $manager->persist($dockerDraft);
        $this->addReference(self::VERSION_DOCKER_DRAFT, $dockerDraft);

        // 创建一个已弃用的版本（测试边界情况）
        $deprecatedVersion = new CourseVersion();
        $deprecatedVersion->setCourse($safetyCourse);
        $deprecatedVersion->setVersion('0.5.0');
        $deprecatedVersion->setTitle('企业安全培训 v0.5（已弃用）');
        $deprecatedVersion->setDescription('旧版本的安全培训课程，已不再使用');
        $deprecatedVersion->setChangeLog('因法规更新，此版本已弃用，请使用最新版本');
        $deprecatedVersion->setStatus('deprecated');
        $deprecatedVersion->setIsCurrent(false);
        $deprecatedVersion->setCourseSnapshot([
            'title' => '旧版安全培训',
            'validDay' => 180,
            'learnHour' => 8,
        ]);
        $deprecatedVersion->setChaptersSnapshot([]);
        $deprecatedVersion->setLessonsSnapshot([]);
        $deprecatedVersion->setPublishedAt(new \DateTimeImmutable('-1 year'));
        $deprecatedVersion->setPublishedBy('old_admin');
        $deprecatedVersion->setMetadata([
            'deprecation_reason' => 'regulatory_changes',
            'superseded_by' => '2024.1',
            'migration_required' => true,
        ]);
        $manager->persist($deprecatedVersion);

        $manager->flush();
    }

    /**
     * 获取课程引用，如果不存在则抛出异常
     *
     * 注意：此方法应仅在开发/测试环境使用，生产环境应确保依赖的 Fixtures 已正确加载
     */
    private function getCourseReference(string $reference): Course
    {
        try {
            return $this->getReference($reference, Course::class);
        } catch (\Exception) {
            throw new \RuntimeException(sprintf('Course reference "%s" not found. Please ensure ChapterFixtures is loaded before CourseVersionFixtures.', $reference));
        }
    }

    public function getDependencies(): array
    {
        return [
            ChapterFixtures::class, // 依赖章节Fixtures，它会创建课程
        ];
    }

    public static function getGroups(): array
    {
        return ['course', 'version', 'history', 'test'];
    }
}
