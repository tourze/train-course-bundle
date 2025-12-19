<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程数据填充
 * 创建不同类型的培训课程，涵盖各种行业和技能领域
 */
#[When(env: 'test')]
#[When(env: 'dev')]
final class CourseFixtures extends Fixture implements FixtureGroupInterface
{
    // 分类类型引用常量
    public const CATALOG_TYPE_COURSE = 'catalog-type-course';

    // 课程引用常量 - 用于其他 Fixture 类引用
    public const COURSE_PHP_BASICS = 'course-php-basics';
    public const COURSE_SYMFONY_ADVANCED = 'course-symfony-advanced';
    public const COURSE_DOCKER_CONTAINER = 'course-docker-container';
    public const COURSE_SAFETY_TRAINING = 'course-safety-training';
    public const COURSE_MANAGEMENT_SKILLS = 'course-management-skills';
    public const COURSE_ELECTRICAL_SAFETY = 'course-electrical-safety';
    public const COURSE_HAZMAT_HANDLING = 'course-hazmat-handling';
    public const COURSE_FIRE_PREVENTION = 'course-fire-prevention';

    public static function getGroups(): array
    {
        return ['course', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        // 创建基础分类（不依赖外部fixtures）
        $uncategorizedCategory = $this->getOrCreateCategory($manager, '未分类', 'uncategorized');
        $mainResponsibleCategory = $this->getOrCreateCategory($manager, '主要负责人', 'main-responsible');
        $specialOperationCategory = $this->getOrCreateCategory($manager, '特种作业人员', 'special-operation');
        $safetyManagementCategory = $this->getOrCreateCategory($manager, '安全管理人员', 'safety-management');
        $hazmatCategory = $this->getOrCreateCategory($manager, '危化品管理', 'hazmat');
        $electricalCategory = $this->getOrCreateCategory($manager, '电气作业', 'electrical');

        // 创建技术类培训课程
        $phpBasicsCourse = new Course();
        $phpBasicsCourse->setCategory($uncategorizedCategory);
        $phpBasicsCourse->setTitle('PHP编程基础入门');
        $phpBasicsCourse->setValidDay(180);
        $phpBasicsCourse->setLearnHour(40);
        $phpBasicsCourse->setTeacherName('张明');
        $phpBasicsCourse->setCoverThumb('https://images.unsplash.com/photo-1599507593548-1d877d4dceb2?w=800&h=450&fit=crop');
        $phpBasicsCourse->setDescription('<p>本课程将带您从零开始学习PHP编程语言的基础知识，包括语法、函数、面向对象编程等核心概念。</p><p>适合编程初学者和希望掌握PHP技能的学员。</p>');
        $phpBasicsCourse->setPrice('199.00');
        $phpBasicsCourse->setValid(true);
        $phpBasicsCourse->setSortNumber(1000);
        $manager->persist($phpBasicsCourse);
        $this->addReference(self::COURSE_PHP_BASICS, $phpBasicsCourse);

        $symfonyAdvancedCourse = new Course();
        $symfonyAdvancedCourse->setCategory($uncategorizedCategory);
        $symfonyAdvancedCourse->setTitle('Symfony框架高级开发实战');
        $symfonyAdvancedCourse->setValidDay(365);
        $symfonyAdvancedCourse->setLearnHour(80);
        $symfonyAdvancedCourse->setTeacherName('李华');
        $symfonyAdvancedCourse->setCoverThumb('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=450&fit=crop');
        $symfonyAdvancedCourse->setDescription('<p>深入学习Symfony框架的高级特性，包括依赖注入、事件系统、缓存机制等企业级开发技术。</p><p>适合有一定PHP基础的开发者进阶学习。</p>');
        $symfonyAdvancedCourse->setPrice('399.00');
        $symfonyAdvancedCourse->setValid(true);
        $symfonyAdvancedCourse->setSortNumber(2000);
        $manager->persist($symfonyAdvancedCourse);
        $this->addReference(self::COURSE_SYMFONY_ADVANCED, $symfonyAdvancedCourse);

        $dockerContainerCourse = new Course();
        $dockerContainerCourse->setCategory($uncategorizedCategory);
        $dockerContainerCourse->setTitle('Docker容器化部署技术');
        $dockerContainerCourse->setValidDay(120);
        $dockerContainerCourse->setLearnHour(32);
        $dockerContainerCourse->setTeacherName('王强');
        $dockerContainerCourse->setCoverThumb('https://images.unsplash.com/photo-1518773553398-650c184e0bb3?w=800&h=450&fit=crop');
        $dockerContainerCourse->setDescription('<p>学习Docker容器技术的核心概念和实际应用，掌握现代应用部署和运维技能。</p><p>包括镜像制作、容器编排、CI/CD集成等实用技术。</p>');
        $dockerContainerCourse->setPrice('299.00');
        $dockerContainerCourse->setValid(true);
        $dockerContainerCourse->setSortNumber(3000);
        $manager->persist($dockerContainerCourse);
        $this->addReference(self::COURSE_DOCKER_CONTAINER, $dockerContainerCourse);

        // 创建安全培训课程
        $safetyTrainingCourse = new Course();
        $safetyTrainingCourse->setCategory($safetyManagementCategory);
        $safetyTrainingCourse->setTitle('企业安全生产管理培训');
        $safetyTrainingCourse->setValidDay(365);
        $safetyTrainingCourse->setLearnHour(24);
        $safetyTrainingCourse->setTeacherName('陈建国');
        $safetyTrainingCourse->setCoverThumb('https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&h=450&fit=crop');
        $safetyTrainingCourse->setDescription('<p>全面的企业安全生产管理知识培训，涵盖安全法规、风险识别、事故预防等关键内容。</p><p>帮助企业管理人员建立完善的安全管理体系。</p>');
        $safetyTrainingCourse->setPrice('149.00');
        $safetyTrainingCourse->setValid(true);
        $safetyTrainingCourse->setSortNumber(4000);
        $manager->persist($safetyTrainingCourse);
        $this->addReference(self::COURSE_SAFETY_TRAINING, $safetyTrainingCourse);

        $managementSkillsCourse = new Course();
        $managementSkillsCourse->setCategory($mainResponsibleCategory);
        $managementSkillsCourse->setTitle('现代企业管理技能提升');
        $managementSkillsCourse->setValidDay(180);
        $managementSkillsCourse->setLearnHour(36);
        $managementSkillsCourse->setTeacherName('刘雅芳');
        $managementSkillsCourse->setCoverThumb('https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&h=450&fit=crop');
        $managementSkillsCourse->setDescription('<p>提升企业中层管理者的综合管理能力，包括团队管理、沟通技巧、决策制定等核心技能。</p><p>结合实际案例，提供可操作的管理工具和方法。</p>');
        $managementSkillsCourse->setPrice('259.00');
        $managementSkillsCourse->setValid(true);
        $managementSkillsCourse->setSortNumber(5000);
        $manager->persist($managementSkillsCourse);
        $this->addReference(self::COURSE_MANAGEMENT_SKILLS, $managementSkillsCourse);

        // 创建特种作业培训课程
        $electricalSafetyCourse = new Course();
        $electricalSafetyCourse->setCategory($electricalCategory);
        $electricalSafetyCourse->setTitle('电工作业安全操作规程');
        $electricalSafetyCourse->setValidDay(365);
        $electricalSafetyCourse->setLearnHour(40);
        $electricalSafetyCourse->setTeacherName('马志强');
        $electricalSafetyCourse->setCoverThumb('https://images.unsplash.com/photo-1605745341112-85968b19335b?w=800&h=450&fit=crop');
        $electricalSafetyCourse->setDescription('<p>电工作业人员必备的安全操作知识和技能培训，包括电气安全基础、操作规程、应急处理等内容。</p><p>符合国家特种作业人员培训要求，助力通过资格考试。</p>');
        $electricalSafetyCourse->setPrice('179.00');
        $electricalSafetyCourse->setValid(true);
        $electricalSafetyCourse->setSortNumber(6000);
        $manager->persist($electricalSafetyCourse);
        $this->addReference(self::COURSE_ELECTRICAL_SAFETY, $electricalSafetyCourse);

        $hazmatHandlingCourse = new Course();
        $hazmatHandlingCourse->setCategory($hazmatCategory);
        $hazmatHandlingCourse->setTitle('危险化学品安全管理');
        $hazmatHandlingCourse->setValidDay(365);
        $hazmatHandlingCourse->setLearnHour(48);
        $hazmatHandlingCourse->setTeacherName('周德胜');
        $hazmatHandlingCourse->setCoverThumb('https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&h=450&fit=crop');
        $hazmatHandlingCourse->setDescription('<p>针对危险化学品从业人员的专业安全培训，涵盖化学品分类、储存运输规定、应急预案等核心内容。</p><p>确保从业人员具备必要的安全意识和操作技能。</p>');
        $hazmatHandlingCourse->setPrice('229.00');
        $hazmatHandlingCourse->setValid(true);
        $hazmatHandlingCourse->setSortNumber(7000);
        $manager->persist($hazmatHandlingCourse);
        $this->addReference(self::COURSE_HAZMAT_HANDLING, $hazmatHandlingCourse);

        $firePreventionCourse = new Course();
        $firePreventionCourse->setCategory($specialOperationCategory);
        $firePreventionCourse->setTitle('消防安全与火灾预防');
        $firePreventionCourse->setValidDay(180);
        $firePreventionCourse->setLearnHour(20);
        $firePreventionCourse->setTeacherName('赵立军');
        $firePreventionCourse->setCoverThumb('https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=450&fit=crop');
        $firePreventionCourse->setDescription('<p>企业消防安全基础知识培训，包括火灾预防、消防设施使用、疏散逃生等实用技能。</p><p>提高员工消防安全意识，确保工作场所安全。</p>');
        $firePreventionCourse->setPrice('99.00');
        $firePreventionCourse->setValid(true);
        $firePreventionCourse->setSortNumber(8000);
        $manager->persist($firePreventionCourse);
        $this->addReference(self::COURSE_FIRE_PREVENTION, $firePreventionCourse);

        $manager->flush();
    }

    /**
     * 获取或创建课程分类类型
     */
    private function getOrCreateCatalogType(ObjectManager $manager): CatalogType
    {
        try {
            return $this->getReference(self::CATALOG_TYPE_COURSE, CatalogType::class);
        } catch (\Exception) {
            $catalogType = new CatalogType();
            $catalogType->setCode('course-fixtures');
            $catalogType->setName('课程分类');
            $manager->persist($catalogType);
            $this->addReference(self::CATALOG_TYPE_COURSE, $catalogType);

            return $catalogType;
        }
    }

    /**
     * 获取或创建分类
     */
    private function getOrCreateCategory(ObjectManager $manager, string $name, string $slug): Catalog
    {
        // 尝试获取已存在的分类
        try {
            return $this->getReference('catalog-' . $slug, Catalog::class);
        } catch (\Exception) {
            // 如果不存在，创建新分类
            $catalogType = $this->getOrCreateCatalogType($manager);

            $category = new Catalog();
            $category->setName($name);
            $category->setSortOrder(1);
            $category->setType($catalogType);
            $category->setCreateTime(new \DateTimeImmutable());
            $category->setUpdateTime(new \DateTimeImmutable());
            $manager->persist($category);

            $this->addReference('catalog-' . $slug, $category);

            return $category;
        }
    }
}
