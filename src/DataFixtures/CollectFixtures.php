<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 课程收藏数据填充
 * 创建示例课程收藏数据，模拟用户收藏行为和收藏分组
 */
#[When(env: 'disabled')]
#[When(env: 'dev')]
class CollectFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 收藏引用常量 - 用于其他 Fixture 类引用
    public const COLLECT_USER1_PHP = 'collect-user1-php';
    public const COLLECT_USER1_SYMFONY = 'collect-user1-symfony';
    public const COLLECT_USER1_DOCKER = 'collect-user1-docker';
    public const COLLECT_USER2_SAFETY = 'collect-user2-safety';
    public const COLLECT_USER2_MANAGEMENT = 'collect-user2-management';
    public const COLLECT_USER3_PHP_CANCELLED = 'collect-user3-php-cancelled';

    public function load(ObjectManager $manager): void
    {
        // 获取课程引用（从 ChapterFixtures 中创建的课程）
        $phpCourse = $this->getCourseReference('php-course');
        $symfonyAdvanced = $this->getCourseReference('symfony-advanced');
        $dockerCourse = $this->getCourseReference('docker-course');
        $safetyCourse = $this->getCourseReference('safety-course');
        $managementCourse = $this->getCourseReference('management-course');

        // 用户1的收藏 - 技术类收藏分组
        $user1PhpCollect = new Collect();
        $user1PhpCollect->setUserId('test_user_001');
        $user1PhpCollect->setCourse($phpCourse);
        $user1PhpCollect->setStatus('active');
        $user1PhpCollect->setCollectGroup('技术学习');
        $user1PhpCollect->setNote('PHP基础很重要，需要反复学习');
        $user1PhpCollect->setSortNumber(10);
        $user1PhpCollect->setIsTop(true);
        $user1PhpCollect->setMetadata([
            'tags' => ['后端开发', 'Web编程', '基础技能'],
            'difficulty' => 'beginner',
            'progress' => 30,
        ]);
        $manager->persist($user1PhpCollect);
        $this->addReference(self::COLLECT_USER1_PHP, $user1PhpCollect);

        $user1SymfonyCollect = new Collect();
        $user1SymfonyCollect->setUserId('test_user_001');
        $user1SymfonyCollect->setCourse($symfonyAdvanced);
        $user1SymfonyCollect->setStatus('active');
        $user1SymfonyCollect->setCollectGroup('技术学习');
        $user1SymfonyCollect->setNote('Symfony框架深入学习');
        $user1SymfonyCollect->setSortNumber(20);
        $user1SymfonyCollect->setIsTop(false);
        $user1SymfonyCollect->setMetadata([
            'tags' => ['PHP框架', '高级开发', 'Web开发'],
            'difficulty' => 'advanced',
            'progress' => 0,
        ]);
        $manager->persist($user1SymfonyCollect);
        $this->addReference(self::COLLECT_USER1_SYMFONY, $user1SymfonyCollect);

        $user1DockerCollect = new Collect();
        $user1DockerCollect->setUserId('test_user_001');
        $user1DockerCollect->setCourse($dockerCourse);
        $user1DockerCollect->setStatus('active');
        $user1DockerCollect->setCollectGroup('运维技术');
        $user1DockerCollect->setNote('容器化部署是趋势，必须掌握');
        $user1DockerCollect->setSortNumber(5);
        $user1DockerCollect->setIsTop(false);
        $user1DockerCollect->setMetadata([
            'tags' => ['容器化', '部署', 'DevOps'],
            'difficulty' => 'intermediate',
            'progress' => 60,
        ]);
        $manager->persist($user1DockerCollect);
        $this->addReference(self::COLLECT_USER1_DOCKER, $user1DockerCollect);

        // 用户2的收藏 - 管理类收藏分组
        $user2SafetyCollect = new Collect();
        $user2SafetyCollect->setUserId('test_user_002');
        $user2SafetyCollect->setCourse($safetyCourse);
        $user2SafetyCollect->setStatus('active');
        $user2SafetyCollect->setCollectGroup('管理培训');
        $user2SafetyCollect->setNote('安全培训必修课程');
        $user2SafetyCollect->setSortNumber(100);
        $user2SafetyCollect->setIsTop(true);
        $user2SafetyCollect->setMetadata([
            'tags' => ['安全管理', '企业培训', '必修课'],
            'difficulty' => 'beginner',
            'progress' => 80,
            'priority' => 'high',
        ]);
        $manager->persist($user2SafetyCollect);
        $this->addReference(self::COLLECT_USER2_SAFETY, $user2SafetyCollect);

        $user2ManagementCollect = new Collect();
        $user2ManagementCollect->setUserId('test_user_002');
        $user2ManagementCollect->setCourse($managementCourse);
        $user2ManagementCollect->setStatus('active');
        $user2ManagementCollect->setCollectGroup('管理培训');
        $user2ManagementCollect->setNote('提升管理技能的好课程');
        $user2ManagementCollect->setSortNumber(50);
        $user2ManagementCollect->setIsTop(false);
        $user2ManagementCollect->setMetadata([
            'tags' => ['管理技能', '领导力', '团队管理'],
            'difficulty' => 'intermediate',
            'progress' => 25,
            'priority' => 'medium',
        ]);
        $manager->persist($user2ManagementCollect);
        $this->addReference(self::COLLECT_USER2_MANAGEMENT, $user2ManagementCollect);

        // 用户3的取消收藏记录 - 用于测试收藏状态变化
        $user3PhpCancelledCollect = new Collect();
        $user3PhpCancelledCollect->setUserId('test_user_003');
        $user3PhpCancelledCollect->setCourse($phpCourse);
        $user3PhpCancelledCollect->setStatus('cancelled');
        $user3PhpCancelledCollect->setCollectGroup('已取消');
        $user3PhpCancelledCollect->setNote('课程内容不够深入，已取消收藏');
        $user3PhpCancelledCollect->setSortNumber(0);
        $user3PhpCancelledCollect->setIsTop(false);
        $user3PhpCancelledCollect->setMetadata([
            'tags' => ['PHP', '基础'],
            'difficulty' => 'beginner',
            'progress' => 10,
            'cancel_reason' => '内容不符合预期',
            'cancelled_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
        $manager->persist($user3PhpCancelledCollect);
        $this->addReference(self::COLLECT_USER3_PHP_CANCELLED, $user3PhpCancelledCollect);

        // 创建一些隐藏状态的收藏（用于测试边界情况）
        $hiddenCollect = new Collect();
        $hiddenCollect->setUserId('test_user_004');
        $hiddenCollect->setCourse($dockerCourse);
        $hiddenCollect->setStatus('hidden');
        $hiddenCollect->setCollectGroup('临时隐藏');
        $hiddenCollect->setNote('暂时隐藏，稍后学习');
        $hiddenCollect->setSortNumber(1);
        $hiddenCollect->setIsTop(false);
        $hiddenCollect->setMetadata([
            'tags' => ['Docker', '容器'],
            'difficulty' => 'intermediate',
            'progress' => 0,
            'hidden_reason' => '时间不够，暂时隐藏',
        ]);
        $manager->persist($hiddenCollect);

        // 批量创建更多收藏数据，模拟真实使用场景
        $userIds = ['test_user_005', 'test_user_006', 'test_user_007'];
        $courses = [$phpCourse, $symfonyAdvanced, $dockerCourse, $safetyCourse, $managementCourse];
        $collectGroups = ['我的收藏', '技术类', '管理类', '待学习', '推荐课程'];

        foreach ($userIds as $index => $userId) {
            // 每个用户随机收藏2-3个课程
            $courseCount = 2 + ($index % 2);
            $randomCourses = array_slice($courses, $index, $courseCount);

            foreach ($randomCourses as $courseIndex => $course) {
                $batchCollect = new Collect();
                $sortNumber = ($index + 1) * 10 + $courseIndex;
                $groupIndex = $courseIndex % count($collectGroups);
                $isTop = (0 === $courseIndex);

                $batchCollect->setUserId($userId);
                $batchCollect->setCourse($course);
                $batchCollect->setStatus('active');
                $batchCollect->setCollectGroup($collectGroups[$groupIndex]);
                $batchCollect->setNote('批量生成的测试收藏数据');
                $batchCollect->setSortNumber($sortNumber);
                $batchCollect->setIsTop($isTop);
                $batchCollect->setMetadata([
                    'batch_generated' => true,
                    'user_index' => $index,
                    'course_index' => $courseIndex,
                    'progress' => rand(0, 100),
                ]);
                $manager->persist($batchCollect);
            }
        }

        $manager->flush();
    }

    /**
     * 获取课程引用，如果不存在则创建简单的测试课程
     *
     * 注意：此方法应仅在开发/测试环境使用，生产环境应确保依赖的 Fixtures 已正确加载
     */
    private function getCourseReference(string $reference): Course
    {
        try {
            return $this->getReference($reference, Course::class);
        } catch (\Exception) {
            throw new \RuntimeException(sprintf('Course reference "%s" not found. Please ensure ChapterFixtures is loaded before CollectFixtures.', $reference));
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
        return ['course', 'collect', 'user', 'test'];
    }
}
