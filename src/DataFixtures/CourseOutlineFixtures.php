<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;

/**
 * 课程大纲数据填充
 * 创建结构化的课程大纲数据，包含学习目标、内容要点、考核标准等
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class CourseOutlineFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 大纲引用常量 - 用于其他 Fixture 类引用
    public const OUTLINE_PHP_INTRO = 'outline-php-intro';
    public const OUTLINE_PHP_ADVANCED = 'outline-php-advanced';
    public const OUTLINE_SYMFONY_BASICS = 'outline-symfony-basics';
    public const OUTLINE_DOCKER_FUNDAMENTALS = 'outline-docker-fundamentals';
    public const OUTLINE_SAFETY_OVERVIEW = 'outline-safety-overview';

    public function load(ObjectManager $manager): void
    {
        // 获取课程引用
        $phpCourse = $this->getCourseReference('php-course');
        $symfonyAdvanced = $this->getCourseReference('symfony-advanced');
        $dockerCourse = $this->getCourseReference('docker-course');
        $safetyCourse = $this->getCourseReference('safety-course');
        $managementCourse = $this->getCourseReference('management-course');

        // 创建 PHP 基础课程大纲
        $phpIntroOutline = new CourseOutline();
        $phpIntroOutline->setCourse($phpCourse);
        $phpIntroOutline->setTitle('PHP语言基础知识大纲');
        $phpIntroOutline->setLearningObjectives(implode("\n", [
            '1. 掌握PHP语言的基本语法和数据类型',
            '2. 理解变量作用域和函数定义方法',
            '3. 熟练使用数组和字符串操作函数',
            '4. 掌握基本的面向对象编程概念',
            '5. 能够独立编写简单的PHP程序',
        ]));
        $phpIntroOutline->setContentPoints(implode("\n", [
            '第一章：PHP环境搭建与基础语法',
            '  - PHP安装与配置',
            '  - 基本语法规则',
            '  - 数据类型详解',
            '',
            '第二章：变量与运算符',
            '  - 变量声明与赋值',
            '  - 运算符使用',
            '  - 类型转换',
            '',
            '第三章：控制结构',
            '  - 条件语句(if/else/switch)',
            '  - 循环语句(for/while/foreach)',
            '  - 跳转语句(break/continue)',
            '',
            '第四章：函数与数组',
            '  - 函数定义与调用',
            '  - 参数传递',
            '  - 数组操作',
        ]));
        $phpIntroOutline->setKeyDifficulties(implode("\n", [
            '重点：',
            '- PHP变量的弱类型特性',
            '- 数组的索引数组和关联数组区别',
            '- 函数作用域的理解',
            '',
            '难点：',
            '- 引用传递与值传递的区别',
            '- 多维数组的操作',
            '- 变量变量的概念',
        ]));
        $phpIntroOutline->setAssessmentCriteria(implode("\n", [
            '理论考核（40%）：',
            '- 基础语法掌握程度：15%',
            '- 数据类型理解：10%',
            '- 函数概念理解：15%',
            '',
            '实践考核（60%）：',
            '- 代码编写规范：20%',
            '- 功能实现正确性：25%',
            '- 问题解决能力：15%',
        ]));
        $phpIntroOutline->setReferences(implode("\n", [
            '主要教材：',
            '- 《PHP权威指南》第5版',
            '- 《Modern PHP》',
            '',
            '参考资料：',
            '- PHP官方文档：https://www.php.net/manual/zh/',
            '- W3School PHP教程',
            '- 菜鸟教程PHP部分',
        ]));
        $phpIntroOutline->setEstimatedMinutes(2400); // 40小时
        $phpIntroOutline->setSortNumber(100);
        $phpIntroOutline->setStatus('published');
        $phpIntroOutline->setMetadata([
            'difficulty_level' => 'beginner',
            'prerequisites' => ['基础计算机知识', 'HTML/CSS基础'],
            'tools_required' => ['XAMPP', 'VS Code', 'Chrome浏览器'],
            'version' => '1.0',
        ]);
        $manager->persist($phpIntroOutline);
        $this->addReference(self::OUTLINE_PHP_INTRO, $phpIntroOutline);

        // 创建 PHP 高级课程大纲
        $phpAdvancedOutline = new CourseOutline();
        $phpAdvancedOutline->setCourse($phpCourse);
        $phpAdvancedOutline->setTitle('PHP高级特性与最佳实践');
        $phpAdvancedOutline->setLearningObjectives(implode("\n", [
            '1. 深入理解PHP面向对象编程',
            '2. 掌握命名空间和自动加载机制',
            '3. 熟练使用Composer包管理器',
            '4. 理解PHP设计模式的应用',
            '5. 掌握异常处理和错误调试技巧',
        ]));
        $phpAdvancedOutline->setContentPoints(implode("\n", [
            '第一章：面向对象进阶',
            '  - 类与对象深入',
            '  - 继承与多态',
            '  - 抽象类与接口',
            '  - Trait使用',
            '',
            '第二章：命名空间与自动加载',
            '  - 命名空间概念',
            '  - PSR-4自动加载',
            '  - Composer使用',
            '',
            '第三章：异常与错误处理',
            '  - 异常机制',
            '  - 自定义异常',
            '  - 错误日志',
        ]));
        $phpAdvancedOutline->setKeyDifficulties(implode("\n", [
            '重点：',
            '- 面向对象编程思想',
            '- 命名空间的正确使用',
            '- Composer依赖管理',
            '',
            '难点：',
            '- 设计模式的理解与应用',
            '- 复杂的类继承关系',
            '- 性能优化技巧',
        ]));
        $phpAdvancedOutline->setAssessmentCriteria(implode("\n", [
            '项目作业（70%）：',
            '- 代码架构设计：25%',
            '- 面向对象使用：20%',
            '- 最佳实践遵循：25%',
            '',
            '理论考试（30%）：',
            '- 概念理解：15%',
            '- 应用场景分析：15%',
        ]));
        $phpAdvancedOutline->setReferences(implode("\n", [
            '进阶教材：',
            '- 《PHP核心技术与最佳实践》',
            '- 《PHP设计模式》',
            '',
            '官方文档：',
            '- PSR标准：https://www.php-fig.org/',
            '- Composer文档：https://getcomposer.org/',
        ]));
        $phpAdvancedOutline->setEstimatedMinutes(1800); // 30小时
        $phpAdvancedOutline->setSortNumber(200);
        $phpAdvancedOutline->setStatus('published');
        $phpAdvancedOutline->setMetadata([
            'difficulty_level' => 'advanced',
            'prerequisites' => ['PHP基础', '面向对象基础概念'],
            'tools_required' => ['PHP 8.0+', 'Composer', 'Git'],
            'version' => '1.1',
        ]);
        $manager->persist($phpAdvancedOutline);
        $this->addReference(self::OUTLINE_PHP_ADVANCED, $phpAdvancedOutline);

        // 创建 Symfony 基础大纲
        $symfonyBasicsOutline = new CourseOutline();
        $symfonyBasicsOutline->setCourse($symfonyAdvanced);
        $symfonyBasicsOutline->setTitle('Symfony框架核心概念');
        $symfonyBasicsOutline->setLearningObjectives(implode("\n", [
            '1. 理解Symfony框架的核心架构',
            '2. 掌握依赖注入容器的使用',
            '3. 熟练使用路由和控制器',
            '4. 掌握Twig模板引擎',
            '5. 理解Symfony的最佳实践',
        ]));
        $symfonyBasicsOutline->setContentPoints(implode("\n", [
            '第一章：Symfony基础',
            '  - 框架简介与安装',
            '  - 目录结构解析',
            '  - 配置系统',
            '',
            '第二章：核心组件',
            '  - HttpFoundation组件',
            '  - HttpKernel组件',
            '  - 依赖注入容器',
            '',
            '第三章：MVC模式实现',
            '  - 路由系统',
            '  - 控制器编写',
            '  - Twig模板',
        ]));
        $symfonyBasicsOutline->setKeyDifficulties(implode("\n", [
            '重点：',
            '- 依赖注入原理',
            '- 服务容器配置',
            '- 路由系统灵活运用',
            '',
            '难点：',
            '- 事件调度器',
            '- 中间件概念',
            '- 复杂的服务配置',
        ]));
        $symfonyBasicsOutline->setAssessmentCriteria(implode("\n", [
            '实战项目（80%）：',
            '- 项目架构：30%',
            '- 代码质量：25%',
            '- 功能完整性：25%',
            '',
            '理论掌握（20%）：',
            '- 概念理解：10%',
            '- 最佳实践：10%',
        ]));
        $symfonyBasicsOutline->setReferences(implode("\n", [
            '官方资料：',
            '- Symfony官方文档',
            '- Symfony最佳实践',
            '',
            '推荐书籍：',
            '- 《Symfony实战》',
            '- 《现代PHP Web开发》',
        ]));
        $symfonyBasicsOutline->setEstimatedMinutes(3000); // 50小时
        $symfonyBasicsOutline->setSortNumber(100);
        $symfonyBasicsOutline->setStatus('published');
        $symfonyBasicsOutline->setMetadata([
            'difficulty_level' => 'intermediate',
            'prerequisites' => ['PHP高级特性', '面向对象编程'],
            'tools_required' => ['PHP 8.1+', 'Composer', 'Symfony CLI'],
            'version' => '2.0',
        ]);
        $manager->persist($symfonyBasicsOutline);
        $this->addReference(self::OUTLINE_SYMFONY_BASICS, $symfonyBasicsOutline);

        // 创建 Docker 基础大纲
        $dockerFundamentalsOutline = new CourseOutline();
        $dockerFundamentalsOutline->setCourse($dockerCourse);
        $dockerFundamentalsOutline->setTitle('Docker容器化技术基础');
        $dockerFundamentalsOutline->setLearningObjectives(implode("\n", [
            '1. 理解容器化技术的核心概念',
            '2. 掌握Docker的基本命令和操作',
            '3. 能够编写Dockerfile创建镜像',
            '4. 掌握Docker Compose多容器编排',
            '5. 了解容器化部署的最佳实践',
        ]));
        $dockerFundamentalsOutline->setContentPoints(implode("\n", [
            '第一章：容器化概述',
            '  - 虚拟化vs容器化',
            '  - Docker架构原理',
            '  - 安装与配置',
            '',
            '第二章：Docker基础操作',
            '  - 镜像管理',
            '  - 容器生命周期',
            '  - 数据卷管理',
            '',
            '第三章：Dockerfile实践',
            '  - 指令详解',
            '  - 最佳实践',
            '  - 多阶段构建',
        ]));
        $dockerFundamentalsOutline->setKeyDifficulties(implode("\n", [
            '重点：',
            '- 镜像与容器的关系',
            '- 数据持久化方案',
            '- 网络配置',
            '',
            '难点：',
            '- 多阶段构建优化',
            '- 容器间通信',
            '- 生产环境部署',
        ]));
        $dockerFundamentalsOutline->setAssessmentCriteria(implode("\n", [
            '实操考核（75%）：',
            '- 容器创建与管理：25%',
            '- Dockerfile编写：25%',
            '- 项目容器化：25%',
            '',
            '理论考核（25%）：',
            '- 概念理解：15%',
            '- 最佳实践：10%',
        ]));
        $dockerFundamentalsOutline->setReferences(implode("\n", [
            '官方文档：',
            '- Docker官方文档',
            '- Docker Hub',
            '',
            '学习资源：',
            '- 《Docker技术入门与实战》',
            '- Docker实战视频教程',
        ]));
        $dockerFundamentalsOutline->setEstimatedMinutes(1200); // 20小时
        $dockerFundamentalsOutline->setSortNumber(100);
        $dockerFundamentalsOutline->setStatus('published');
        $dockerFundamentalsOutline->setMetadata([
            'difficulty_level' => 'intermediate',
            'prerequisites' => ['Linux基础', '基本的命令行操作'],
            'tools_required' => ['Docker Desktop', 'Linux/Windows/Mac'],
            'version' => '1.0',
        ]);
        $manager->persist($dockerFundamentalsOutline);
        $this->addReference(self::OUTLINE_DOCKER_FUNDAMENTALS, $dockerFundamentalsOutline);

        // 创建安全培训大纲
        $safetyOverviewOutline = new CourseOutline();
        $safetyOverviewOutline->setCourse($safetyCourse);
        $safetyOverviewOutline->setTitle('企业安全培训基础大纲');
        $safetyOverviewOutline->setLearningObjectives(implode("\n", [
            '1. 建立全面的安全意识',
            '2. 掌握基本安全操作规程',
            '3. 了解应急处理程序',
            '4. 熟悉安全法规要求',
            '5. 能够识别和预防安全隐患',
        ]));
        $safetyOverviewOutline->setContentPoints(implode("\n", [
            '第一章：安全基础知识',
            '  - 安全管理体系',
            '  - 法律法规要求',
            '  - 安全责任制',
            '',
            '第二章：作业安全',
            '  - 个人防护用品',
            '  - 安全操作规程',
            '  - 危险源识别',
            '',
            '第三章：应急响应',
            '  - 事故预防',
            '  - 应急预案',
            '  - 自救互救',
        ]));
        $safetyOverviewOutline->setKeyDifficulties(implode("\n", [
            '重点：',
            '- 安全意识培养',
            '- 规程严格执行',
            '- 隐患识别能力',
            '',
            '难点：',
            '- 复杂环境风险评估',
            '- 应急情况快速决策',
            '- 团队协作救援',
        ]));
        $safetyOverviewOutline->setAssessmentCriteria(implode("\n", [
            '理论考试（40%）：',
            '- 法规掌握：20%',
            '- 安全知识：20%',
            '',
            '实操考核（60%）：',
            '- 防护用品使用：20%',
            '- 应急演练：20%',
            '- 隐患识别：20%',
        ]));
        $safetyOverviewOutline->setReferences(implode("\n", [
            '法规标准：',
            '- 《安全生产法》',
            '- GB/T28001职业健康安全管理体系',
            '- 行业安全规程',
            '',
            '培训教材：',
            '- 企业安全生产标准化指南',
            '- 应急救援实用手册',
        ]));
        $safetyOverviewOutline->setEstimatedMinutes(480); // 8小时
        $safetyOverviewOutline->setSortNumber(100);
        $safetyOverviewOutline->setStatus('published');
        $safetyOverviewOutline->setMetadata([
            'difficulty_level' => 'beginner',
            'prerequisites' => ['基本工作能力'],
            'compliance_required' => true,
            'certificate_type' => 'safety_training',
            'version' => '2024.1',
        ]);
        $manager->persist($safetyOverviewOutline);
        $this->addReference(self::OUTLINE_SAFETY_OVERVIEW, $safetyOverviewOutline);

        // 创建草稿状态的大纲（用于测试）
        $draftOutline = new CourseOutline();
        $draftOutline->setCourse($managementCourse);
        $draftOutline->setTitle('管理技能提升大纲（草稿）');
        $draftOutline->setLearningObjectives('待完善的学习目标');
        $draftOutline->setContentPoints('待补充的内容要点');
        $draftOutline->setEstimatedMinutes(600);
        $draftOutline->setSortNumber(100);
        $draftOutline->setStatus('draft');
        $draftOutline->setMetadata(['draft_version' => true, 'completion_rate' => 30]);
        $manager->persist($draftOutline);

        $manager->flush();
    }

    /**
     * 获取课程引用，如果不存在则创建一个
     */
    private function getCourseReference(string $reference): Course
    {
        try {
            return $this->getReference($reference, Course::class);
        } catch (\Exception) {
            // 如果课程引用不存在，创建一个简单的测试课程
            $course = new Course();
            $course->setTitle('测试课程 - ' . $reference);
            $course->setValidDay(365);
            $course->setLearnHour(20);
            $course->setDescription('用于 CourseOutlineFixtures 测试的示例课程');
            $course->setValid(true);

            // 需要设置课程的分类才能持久化
            // 这里会抛出异常，因为缺少必需的分类
            // 但这是预期的行为，因为课程应该由 ChapterFixtures 创建

            return $course;
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
        return ['course', 'outline', 'curriculum', 'test'];
    }
}
