<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * 课程评价数据填充
 * 创建不同类型的课程评价数据，包括各种评分、评价状态和用户反馈
 */
#[When(env: 'test')]
class EvaluateFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 评价引用常量 - 用于其他 Fixture 类引用
    public const EVALUATE_PHP_EXCELLENT = 'evaluate-php-excellent';
    public const EVALUATE_PHP_GOOD = 'evaluate-php-good';
    public const EVALUATE_SYMFONY_AVERAGE = 'evaluate-symfony-average';
    public const EVALUATE_DOCKER_POOR = 'evaluate-docker-poor';
    public const EVALUATE_SAFETY_PENDING = 'evaluate-safety-pending';

    public function load(ObjectManager $manager): void
    {
        // 获取课程实体用于创建评价
        /** @var Course $phpCourse */
        $phpCourse = $this->getReference(CourseFixtures::COURSE_PHP_BASICS, Course::class);

        // 创建优秀评价
        $excellentEvaluate = new Evaluate();
        $excellentEvaluate->setUserId('user_001');
        $excellentEvaluate->setCourse($phpCourse);
        $excellentEvaluate->setRating(5);
        $excellentEvaluate->setContent('这门课程讲得非常好，内容详细，案例丰富！');
        $excellentEvaluate->setStatus('published');
        $excellentEvaluate->setUserNickname('学习达人');
        $excellentEvaluate->setIsAnonymous(false);
        $excellentEvaluate->setLikeCount(15);
        $excellentEvaluate->setReplyCount(3);
        $excellentEvaluate->setCreateTime(new \DateTimeImmutable('2024-01-15 10:30:00'));

        $manager->persist($excellentEvaluate);
        $this->addReference(self::EVALUATE_PHP_EXCELLENT, $excellentEvaluate);

        // 创建良好评价
        $goodEvaluate = new Evaluate();
        $goodEvaluate->setUserId('user_002');
        $goodEvaluate->setCourse($phpCourse);
        $goodEvaluate->setRating(4);
        $goodEvaluate->setContent('课程整体很不错，讲解清晰，值得推荐。');
        $goodEvaluate->setStatus('published');
        $goodEvaluate->setUserNickname('PHP学习者');
        $goodEvaluate->setIsAnonymous(false);
        $goodEvaluate->setLikeCount(8);
        $goodEvaluate->setReplyCount(1);
        $goodEvaluate->setCreateTime(new \DateTimeImmutable('2024-01-16 14:20:00'));

        $manager->persist($goodEvaluate);
        $this->addReference(self::EVALUATE_PHP_GOOD, $goodEvaluate);

        // 创建一般评价（如果有其他课程的话）
        try {
            /** @var Course $symfonyCourse */
            $symfonyCourse = $this->getReference(CourseFixtures::COURSE_SYMFONY_ADVANCED, Course::class);

            $averageEvaluate = new Evaluate();
            $averageEvaluate->setUserId('user_003');
            $averageEvaluate->setCourse($symfonyCourse);
            $averageEvaluate->setRating(3);
            $averageEvaluate->setContent('课程内容一般，有些地方讲得不够深入。');
            $averageEvaluate->setStatus('published');
            $averageEvaluate->setUserNickname('普通学员');
            $averageEvaluate->setIsAnonymous(true);
            $averageEvaluate->setLikeCount(2);
            $averageEvaluate->setReplyCount(0);
            $averageEvaluate->setCreateTime(new \DateTimeImmutable('2024-01-17 16:45:00'));

            $manager->persist($averageEvaluate);
            $this->addReference(self::EVALUATE_SYMFONY_AVERAGE, $averageEvaluate);
        } catch (\Exception) {
            // 如果引用不存在，跳过创建这个评价
        }

        // 创建待审核评价
        $pendingEvaluate = new Evaluate();
        $pendingEvaluate->setUserId('user_004');
        $pendingEvaluate->setCourse($phpCourse);
        $pendingEvaluate->setRating(5);
        $pendingEvaluate->setContent('等待审核的评价内容...');
        $pendingEvaluate->setStatus('pending');
        $pendingEvaluate->setUserNickname('新用户');
        $pendingEvaluate->setIsAnonymous(false);
        $pendingEvaluate->setLikeCount(0);
        $pendingEvaluate->setReplyCount(0);
        $pendingEvaluate->setCreateTime(new \DateTimeImmutable('2024-01-18 09:15:00'));

        $manager->persist($pendingEvaluate);
        $this->addReference(self::EVALUATE_SAFETY_PENDING, $pendingEvaluate);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CourseFixtures::class, // 依赖课程Fixtures
        ];
    }

    public static function getGroups(): array
    {
        return ['course', 'evaluate', 'feedback', 'test'];
    }
}
