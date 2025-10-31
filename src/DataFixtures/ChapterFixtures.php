<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程章节数据填充
 * 为测试课程创建合理的章节结构，包含不同类型的学习内容
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class ChapterFixtures extends Fixture implements FixtureGroupInterface
{
    // 章节引用常量 - 用于其他 Fixture 类引用
    public const CHAPTER_PHP_BASICS = 'chapter-php-basics';
    public const CHAPTER_PHP_ADVANCED = 'chapter-php-advanced';
    public const CHAPTER_SYMFONY_INTRO = 'chapter-symfony-intro';
    public const CHAPTER_SYMFONY_PRACTICE = 'chapter-symfony-practice';
    public const CHAPTER_DOCKER_BASICS = 'chapter-docker-basics';
    public const CHAPTER_SAFETY_RULES = 'chapter-safety-rules';
    public const CHAPTER_SAFETY_PRACTICE = 'chapter-safety-practice';
    public const CHAPTER_MANAGEMENT_THEORY = 'chapter-management-theory';

    // 分类引用常量
    public const CATALOG_COURSE_TEST = 'catalog-course-test';

    public function load(ObjectManager $manager): void
    {
        // 创建示例课程用于测试（如果没有的话）
        $phpCourse = $this->createTestCourse($manager, 'PHP基础入门', 'php-course');
        $symfonyAdvanced = $this->createTestCourse($manager, 'Symfony高级开发', 'symfony-advanced');
        $dockerCourse = $this->createTestCourse($manager, 'Docker容器化部署', 'docker-course');
        $safetyCourse = $this->createTestCourse($manager, '企业安全培训', 'safety-course');
        $managementCourse = $this->createTestCourse($manager, '管理技能提升', 'management-course');

        // 创建 PHP 基础课程章节
        $phpBasicsChapter = new Chapter();
        $phpBasicsChapter->setCourse($phpCourse);
        $phpBasicsChapter->setTitle('PHP语言基础');
        $phpBasicsChapter->setSortNumber(100);
        $manager->persist($phpBasicsChapter);
        $this->addReference(self::CHAPTER_PHP_BASICS, $phpBasicsChapter);

        $phpAdvancedChapter = new Chapter();
        $phpAdvancedChapter->setCourse($phpCourse);
        $phpAdvancedChapter->setTitle('PHP高级特性');
        $phpAdvancedChapter->setSortNumber(200);
        $manager->persist($phpAdvancedChapter);
        $this->addReference(self::CHAPTER_PHP_ADVANCED, $phpAdvancedChapter);

        // 创建 Symfony 高级课程章节
        $symfonyIntroChapter = new Chapter();
        $symfonyIntroChapter->setCourse($symfonyAdvanced);
        $symfonyIntroChapter->setTitle('Symfony框架介绍');
        $symfonyIntroChapter->setSortNumber(100);
        $manager->persist($symfonyIntroChapter);
        $this->addReference(self::CHAPTER_SYMFONY_INTRO, $symfonyIntroChapter);

        $symfonyPracticeChapter = new Chapter();
        $symfonyPracticeChapter->setCourse($symfonyAdvanced);
        $symfonyPracticeChapter->setTitle('Symfony实战开发');
        $symfonyPracticeChapter->setSortNumber(200);
        $manager->persist($symfonyPracticeChapter);
        $this->addReference(self::CHAPTER_SYMFONY_PRACTICE, $symfonyPracticeChapter);

        // 创建 Docker 课程章节
        $dockerBasicsChapter = new Chapter();
        $dockerBasicsChapter->setCourse($dockerCourse);
        $dockerBasicsChapter->setTitle('Docker容器基础');
        $dockerBasicsChapter->setSortNumber(100);
        $manager->persist($dockerBasicsChapter);
        $this->addReference(self::CHAPTER_DOCKER_BASICS, $dockerBasicsChapter);

        // 创建安全培训课程章节
        $safetyRulesChapter = new Chapter();
        $safetyRulesChapter->setCourse($safetyCourse);
        $safetyRulesChapter->setTitle('安全规章制度');
        $safetyRulesChapter->setSortNumber(100);
        $manager->persist($safetyRulesChapter);
        $this->addReference(self::CHAPTER_SAFETY_RULES, $safetyRulesChapter);

        $safetyPracticeChapter = new Chapter();
        $safetyPracticeChapter->setCourse($safetyCourse);
        $safetyPracticeChapter->setTitle('安全实操演练');
        $safetyPracticeChapter->setSortNumber(200);
        $manager->persist($safetyPracticeChapter);
        $this->addReference(self::CHAPTER_SAFETY_PRACTICE, $safetyPracticeChapter);

        // 创建管理技能课程章节
        $managementTheoryChapter = new Chapter();
        $managementTheoryChapter->setCourse($managementCourse);
        $managementTheoryChapter->setTitle('管理理论基础');
        $managementTheoryChapter->setSortNumber(100);
        $manager->persist($managementTheoryChapter);
        $this->addReference(self::CHAPTER_MANAGEMENT_THEORY, $managementTheoryChapter);

        $manager->flush();
    }

    /**
     * 创建测试课程（如果不存在的话）
     */
    private function createTestCourse(ObjectManager $manager, string $title, string $reference): Course
    {
        try {
            return $this->getReference($reference, Course::class);
        } catch (\Exception) {
            // 如果课程不存在，创建一个简单的测试课程
            $course = new Course();
            $course->setTitle($title);
            $course->setValidDay(365);
            $course->setLearnHour(40);
            $course->setTeacherName('测试讲师');
            $course->setDescription('这是一个用于DataFixtures测试的示例课程');
            $course->setValid(true);

            // 使用预定义的引用，避免数据库查询
            try {
                $category = $this->getReference(self::CATALOG_COURSE_TEST, Catalog::class);
            } catch (\Exception) {
                // 如果引用不存在，创建默认分类（延迟 flush 到主 load 方法）
                $catalogType = new CatalogType();
                $catalogType->setCode('course-chapter-fixtures');
                $catalogType->setName('课程分类');
                $manager->persist($catalogType);

                $category = new Catalog();
                $category->setName('测试分类');
                $category->setSortOrder(1);
                $category->setType($catalogType);
                $manager->persist($category);
                $this->addReference(self::CATALOG_COURSE_TEST, $category);
            }
            $course->setCategory($category);

            $manager->persist($course);
            $this->addReference($reference, $course);

            return $course;
        }
    }

    public static function getGroups(): array
    {
        return ['course', 'chapter', 'test'];
    }
}
