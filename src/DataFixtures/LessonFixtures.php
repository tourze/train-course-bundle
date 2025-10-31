<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课时数据填充
 * 为各个章节创建详细的课时内容，包含视频、时长等信息
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class LessonFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 课时引用常量 - 用于其他 Fixture 类引用
    public const LESSON_PHP_SYNTAX = 'lesson-php-syntax';
    public const LESSON_PHP_FUNCTIONS = 'lesson-php-functions';
    public const LESSON_PHP_OOP = 'lesson-php-oop';
    public const LESSON_SYMFONY_COMPONENTS = 'lesson-symfony-components';
    public const LESSON_SYMFONY_ROUTING = 'lesson-symfony-routing';
    public const LESSON_DOCKER_BASICS = 'lesson-docker-basics';
    public const LESSON_DOCKER_COMPOSE = 'lesson-docker-compose';
    public const LESSON_SAFETY_REGULATIONS = 'lesson-safety-regulations';
    public const LESSON_RISK_ASSESSMENT = 'lesson-risk-assessment';
    public const LESSON_MANAGEMENT_PRINCIPLES = 'lesson-management-principles';

    public static function getGroups(): array
    {
        return ['course', 'lesson', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        // 获取章节引用
        $phpBasicsChapter = $this->getReference(ChapterFixtures::CHAPTER_PHP_BASICS, Chapter::class);
        $phpAdvancedChapter = $this->getReference(ChapterFixtures::CHAPTER_PHP_ADVANCED, Chapter::class);
        $symfonyIntroChapter = $this->getReference(ChapterFixtures::CHAPTER_SYMFONY_INTRO, Chapter::class);
        $symfonyPracticeChapter = $this->getReference(ChapterFixtures::CHAPTER_SYMFONY_PRACTICE, Chapter::class);
        $dockerBasicsChapter = $this->getReference(ChapterFixtures::CHAPTER_DOCKER_BASICS, Chapter::class);
        $safetyRulesChapter = $this->getReference(ChapterFixtures::CHAPTER_SAFETY_RULES, Chapter::class);
        $safetyPracticeChapter = $this->getReference(ChapterFixtures::CHAPTER_SAFETY_PRACTICE, Chapter::class);
        $managementTheoryChapter = $this->getReference(ChapterFixtures::CHAPTER_MANAGEMENT_THEORY, Chapter::class);

        // 为 PHP 基础章节创建课时
        $this->createPhpBasicsLessons($manager, $phpBasicsChapter);

        // 为 PHP 高级章节创建课时
        $this->createPhpAdvancedLessons($manager, $phpAdvancedChapter);

        // 为 Symfony 介绍章节创建课时
        $this->createSymfonyIntroLessons($manager, $symfonyIntroChapter);

        // 为 Symfony 实战章节创建课时
        $this->createSymfonyPracticeLessons($manager, $symfonyPracticeChapter);

        // 为 Docker 基础章节创建课时
        $this->createDockerBasicsLessons($manager, $dockerBasicsChapter);

        // 为安全规章章节创建课时
        $this->createSafetyRulesLessons($manager, $safetyRulesChapter);

        // 为安全实操章节创建课时
        $this->createSafetyPracticeLessons($manager, $safetyPracticeChapter);

        // 为管理理论章节创建课时
        $this->createManagementTheoryLessons($manager, $managementTheoryChapter);

        $manager->flush();
    }

    /**
     * 创建 PHP 基础课时
     */
    private function createPhpBasicsLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => 'PHP语法基础',
                'duration' => 1800, // 30分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1599507593548-1d877d4dceb2?w=400&h=300&fit=crop',
                'sortNumber' => 100,
                'reference' => self::LESSON_PHP_SYNTAX,
            ],
            [
                'title' => '变量与数据类型',
                'duration' => 2400, // 40分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=300&fit=crop',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => '控制结构',
                'duration' => 2700, // 45分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1518773553398-650c184e0bb3?w=400&h=300&fit=crop',
                'sortNumber' => 300,
                'reference' => null,
            ],
            [
                'title' => '函数的定义与使用',
                'duration' => 3600, // 60分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400&h=300&fit=crop',
                'sortNumber' => 400,
                'reference' => self::LESSON_PHP_FUNCTIONS,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 从数组创建课时
     *
     * @param array<int, array{title: string, duration: int, videoUrl: string|null, coverThumb?: string, sortNumber?: int, reference?: string|null}> $lessons
     */
    private function createLessonsFromArray(ObjectManager $manager, Chapter $chapter, array $lessons): void
    {
        foreach ($lessons as $lessonData) {
            $lesson = new Lesson();
            $lesson->setChapter($chapter);
            $lesson->setTitle($lessonData['title']);
            $lesson->setDurationSecond($lessonData['duration']);
            $lesson->setVideoUrl($lessonData['videoUrl']);
            $lesson->setFaceDetectDuration(900); // 默认15分钟人脸识别间隔

            // 设置可选属性
            if (isset($lessonData['coverThumb'])) {
                $lesson->setCoverThumb($lessonData['coverThumb']);
            }

            if (isset($lessonData['sortNumber'])) {
                $lesson->setSortNumber($lessonData['sortNumber']);
            }

            $manager->persist($lesson);

            // 如果有引用名称，添加引用
            if (isset($lessonData['reference']) && '' !== $lessonData['reference']) {
                $this->addReference($lessonData['reference'], $lesson);
            }
        }
    }

    /**
     * 创建 PHP 高级课时
     */
    private function createPhpAdvancedLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => '面向对象编程基础',
                'duration' => 4500, // 75分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=400&h=300&fit=crop',
                'sortNumber' => 100,
                'reference' => self::LESSON_PHP_OOP,
            ],
            [
                'title' => '继承与多态',
                'duration' => 3900, // 65分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1605745341112-85968b19335b?w=400&h=300&fit=crop',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => '命名空间与自动加载',
                'duration' => 2700, // 45分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=400&h=300&fit=crop',
                'sortNumber' => 300,
                'reference' => null,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 创建 Symfony 介绍课时
     */
    private function createSymfonyIntroLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => 'Symfony框架概述',
                'duration' => 2400, // 40分钟
                'videoUrl' => null,
                'coverThumb' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop',
                'sortNumber' => 100,
                'reference' => null,
            ],
            [
                'title' => 'Symfony组件架构',
                'duration' => 3600, // 60分钟
                'videoUrl' => 'symfony-components',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsymfony-components',
                'sortNumber' => 200,
                'reference' => self::LESSON_SYMFONY_COMPONENTS,
            ],
            [
                'title' => '环境配置与项目搭建',
                'duration' => 2700, // 45分钟
                'videoUrl' => 'symfony-setup',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsymfony-setup',
                'sortNumber' => 300,
                'reference' => null,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 创建 Symfony 实战课时
     */
    private function createSymfonyPracticeLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => '路由系统详解',
                'duration' => 4200, // 70分钟
                'videoUrl' => 'symfony-routing',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsymfony-routing',
                'sortNumber' => 100,
                'reference' => self::LESSON_SYMFONY_ROUTING,
            ],
            [
                'title' => '控制器与模板',
                'duration' => 3900, // 65分钟
                'videoUrl' => 'symfony-controller',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsymfony-controller',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => 'Doctrine ORM 应用',
                'duration' => 5400, // 90分钟
                'videoUrl' => 'symfony-doctrine',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsymfony-doctrine',
                'sortNumber' => 300,
                'reference' => null,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 创建 Docker 基础课时
     */
    private function createDockerBasicsLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => 'Docker基本概念',
                'duration' => 2100, // 35分钟
                'videoUrl' => 'docker-concepts',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatdocker-concepts',
                'sortNumber' => 100,
                'reference' => self::LESSON_DOCKER_BASICS,
            ],
            [
                'title' => '镜像构建与管理',
                'duration' => 3300, // 55分钟
                'videoUrl' => 'docker-images',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatdocker-images',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => 'Docker Compose应用',
                'duration' => 4800, // 80分钟
                'videoUrl' => 'docker-compose',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatdocker-compose',
                'sortNumber' => 300,
                'reference' => self::LESSON_DOCKER_COMPOSE,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 创建安全规章课时
     */
    private function createSafetyRulesLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => '安全生产法律法规',
                'duration' => 2700, // 45分钟
                'videoUrl' => 'safety-regulations',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsafety-regulations',
                'sortNumber' => 100,
                'reference' => self::LESSON_SAFETY_REGULATIONS,
            ],
            [
                'title' => '企业安全管理制度',
                'duration' => 3600, // 60分钟
                'videoUrl' => 'safety-management',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsafety-management',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => '员工安全职责',
                'duration' => 1800, // 30分钟
                'videoUrl' => 'safety-responsibilities',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsafety-responsibilities',
                'sortNumber' => 300,
                'reference' => null,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 创建安全实操课时
     */
    private function createSafetyPracticeLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => '危险源识别与评估',
                'duration' => 4200, // 70分钟
                'videoUrl' => 'risk-assessment',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatrisk-assessment',
                'sortNumber' => 100,
                'reference' => self::LESSON_RISK_ASSESSMENT,
            ],
            [
                'title' => '应急处置演练',
                'duration' => 3900, // 65分钟
                'videoUrl' => 'emergency-drill',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatemergency-drill',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => '安全设备使用',
                'duration' => 2700, // 45分钟
                'videoUrl' => 'safety-equipment',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatsafety-equipment',
                'sortNumber' => 300,
                'reference' => null,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    /**
     * 创建管理理论课时
     */
    private function createManagementTheoryLessons(ObjectManager $manager, Chapter $chapter): void
    {
        $lessons = [
            [
                'title' => '现代管理理论基础',
                'duration' => 3600, // 60分钟
                'videoUrl' => 'management-principles',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatmanagement-principles',
                'sortNumber' => 100,
                'reference' => self::LESSON_MANAGEMENT_PRINCIPLES,
            ],
            [
                'title' => '团队建设与领导力',
                'duration' => 4500, // 75分钟
                'videoUrl' => 'team-leadership',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatteam-leadership',
                'sortNumber' => 200,
                'reference' => null,
            ],
            [
                'title' => '绩效管理与激励机制',
                'duration' => 3900, // 65分钟
                'videoUrl' => 'performance-management',
                'coverThumb' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=400&h=300&fit=crop&auto=formatperformance-management',
                'sortNumber' => 300,
                'reference' => null,
            ],
        ];

        $this->createLessonsFromArray($manager, $chapter, $lessons);
    }

    public function getDependencies(): array
    {
        return [
            ChapterFixtures::class,
        ];
    }
}
