<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;

/**
 * 课程播放控制数据填充
 * 创建不同级别的播放控制策略，包括严格模式、标准模式和宽松模式
 */
#[When(env: 'disabled')]
#[When(env: 'dev')]
class CoursePlayControlFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 播放控制引用常量 - 用于其他 Fixture 类引用
    public const CONTROL_PHP_STRICT = 'control-php-strict';
    public const CONTROL_SYMFONY_STANDARD = 'control-symfony-standard';
    public const CONTROL_DOCKER_FLEXIBLE = 'control-docker-flexible';
    public const CONTROL_SAFETY_COMPLIANCE = 'control-safety-compliance';
    public const CONTROL_MANAGEMENT_BASIC = 'control-management-basic';

    public function load(ObjectManager $manager): void
    {
        // 获取课程引用
        $phpCourse = $this->getCourseReference('php-course');
        $symfonyAdvanced = $this->getCourseReference('symfony-advanced');
        $dockerCourse = $this->getCourseReference('docker-course');
        $safetyCourse = $this->getCourseReference('safety-course');
        $managementCourse = $this->getCourseReference('management-course');

        // 创建严格模式播放控制 - PHP课程
        $phpStrictControl = new CoursePlayControl();
        $phpStrictControl->setCourse($phpCourse);
        $phpStrictControl->setEnabled(true);
        $phpStrictControl->setAllowFastForward(false); // 禁止快进
        $phpStrictControl->setAllowSpeedControl(false); // 禁止倍速
        $phpStrictControl->setAllowedSpeeds(null); // 不允许调速
        $phpStrictControl->setEnableWatermark(true); // 启用水印
        $phpStrictControl->setWatermarkText('PHP基础培训 - 禁止快进');
        $phpStrictControl->setWatermarkPosition('center');
        $phpStrictControl->setWatermarkOpacity(30);
        $phpStrictControl->setMaxDeviceCount(2); // 限制2台设备
        $phpStrictControl->setPlayAuthDuration(1800); // 30分钟播放凭证
        $phpStrictControl->setEnableResume(true); // 允许续播
        $phpStrictControl->setMinWatchDuration(300); // 最少观看5分钟
        $phpStrictControl->setProgressCheckInterval(15); // 每15秒检查进度
        $phpStrictControl->setAllowSeeking(false); // 禁止拖拽
        $phpStrictControl->setAllowContextMenu(false); // 禁用右键菜单
        $phpStrictControl->setAllowDownload(false); // 禁止下载
        $phpStrictControl->setExtendedConfig([
            'anti_cheat_mode' => true,
            'screenshot_detection' => true,
            'focus_detection' => true,
            'minimum_window_size' => [800, 600],
        ]);
        $phpStrictControl->setMetadata([
            'control_level' => 'strict',
            'compliance_required' => true,
            'monitoring_enabled' => true,
            'purpose' => 'formal_training',
        ]);
        $manager->persist($phpStrictControl);
        $this->addReference(self::CONTROL_PHP_STRICT, $phpStrictControl);

        // 创建标准模式播放控制 - Symfony课程
        $symfonyStandardControl = new CoursePlayControl();
        $symfonyStandardControl->setCourse($symfonyAdvanced);
        $symfonyStandardControl->setEnabled(true);
        $symfonyStandardControl->setAllowFastForward(false); // 禁止快进
        $symfonyStandardControl->setAllowSpeedControl(true); // 允许倍速
        $symfonyStandardControl->setAllowedSpeeds([0.75, 1.0, 1.25, 1.5]); // 限制倍速范围
        $symfonyStandardControl->setEnableWatermark(true);
        $symfonyStandardControl->setWatermarkText('Symfony高级开发');
        $symfonyStandardControl->setWatermarkPosition('bottom-right');
        $symfonyStandardControl->setWatermarkOpacity(50);
        $symfonyStandardControl->setMaxDeviceCount(3); // 允许3台设备
        $symfonyStandardControl->setPlayAuthDuration(3600); // 1小时播放凭证
        $symfonyStandardControl->setEnableResume(true);
        $symfonyStandardControl->setMinWatchDuration(180); // 最少观看3分钟
        $symfonyStandardControl->setProgressCheckInterval(30); // 每30秒检查
        $symfonyStandardControl->setAllowSeeking(false); // 禁止拖拽
        $symfonyStandardControl->setAllowContextMenu(false);
        $symfonyStandardControl->setAllowDownload(false);
        $symfonyStandardControl->setExtendedConfig([
            'quality_control' => 'adaptive',
            'buffer_time' => 30,
            'retry_attempts' => 3,
        ]);
        $symfonyStandardControl->setMetadata([
            'control_level' => 'standard',
            'course_type' => 'professional',
            'certification_required' => true,
        ]);
        $manager->persist($symfonyStandardControl);
        $this->addReference(self::CONTROL_SYMFONY_STANDARD, $symfonyStandardControl);

        // 创建灵活模式播放控制 - Docker课程
        $dockerFlexibleControl = new CoursePlayControl();
        $dockerFlexibleControl->setCourse($dockerCourse);
        $dockerFlexibleControl->setEnabled(true);
        $dockerFlexibleControl->setAllowFastForward(true); // 允许快进
        $dockerFlexibleControl->setAllowSpeedControl(true); // 允许倍速
        $dockerFlexibleControl->setAllowedSpeeds([0.5, 0.75, 1.0, 1.25, 1.5, 2.0]); // 完整倍速范围
        $dockerFlexibleControl->setEnableWatermark(true);
        $dockerFlexibleControl->setWatermarkText('Docker容器化实战');
        $dockerFlexibleControl->setWatermarkPosition('top-left');
        $dockerFlexibleControl->setWatermarkOpacity(70);
        $dockerFlexibleControl->setMaxDeviceCount(5); // 允许5台设备
        $dockerFlexibleControl->setPlayAuthDuration(7200); // 2小时播放凭证
        $dockerFlexibleControl->setEnableResume(true);
        $dockerFlexibleControl->setMinWatchDuration(60); // 最少观看1分钟
        $dockerFlexibleControl->setProgressCheckInterval(60); // 每60秒检查
        $dockerFlexibleControl->setAllowSeeking(true); // 允许拖拽
        $dockerFlexibleControl->setAllowContextMenu(false); // 仍然禁用右键
        $dockerFlexibleControl->setAllowDownload(false); // 禁止下载
        $dockerFlexibleControl->setExtendedConfig([
            'chapter_skip_allowed' => true,
            'bookmark_enabled' => true,
            'note_taking_enabled' => true,
            'playback_history' => true,
        ]);
        $dockerFlexibleControl->setMetadata([
            'control_level' => 'flexible',
            'course_type' => 'technical',
            'self_paced' => true,
            'interactive_features' => true,
        ]);
        $manager->persist($dockerFlexibleControl);
        $this->addReference(self::CONTROL_DOCKER_FLEXIBLE, $dockerFlexibleControl);

        // 创建合规模式播放控制 - 安全培训课程
        $safetyComplianceControl = new CoursePlayControl();
        $safetyComplianceControl->setCourse($safetyCourse);
        $safetyComplianceControl->setEnabled(true);
        $safetyComplianceControl->setAllowFastForward(false); // 严格禁止快进
        $safetyComplianceControl->setAllowSpeedControl(false); // 严格禁止倍速
        $safetyComplianceControl->setAllowedSpeeds([1.0]); // 只允许正常速度
        $safetyComplianceControl->setEnableWatermark(true);
        $safetyComplianceControl->setWatermarkText('企业安全培训 - 法规要求完整观看');
        $safetyComplianceControl->setWatermarkPosition('center');
        $safetyComplianceControl->setWatermarkOpacity(25);
        $safetyComplianceControl->setMaxDeviceCount(1); // 严格限制1台设备
        $safetyComplianceControl->setPlayAuthDuration(900); // 15分钟播放凭证（需要频繁验证）
        $safetyComplianceControl->setEnableResume(false); // 不允许续播，必须连续观看
        $safetyComplianceControl->setMinWatchDuration(600); // 最少观看10分钟
        $safetyComplianceControl->setProgressCheckInterval(10); // 每10秒检查进度
        $safetyComplianceControl->setAllowSeeking(false); // 严格禁止拖拽
        $safetyComplianceControl->setAllowContextMenu(false);
        $safetyComplianceControl->setAllowDownload(false);
        $safetyComplianceControl->setExtendedConfig([
            'compliance_mode' => true,
            'mandatory_completion' => true,
            'attention_check_enabled' => true,
            'idle_timeout' => 300, // 5分钟无操作超时
            'screenshot_blocked' => true,
            'copy_protection' => true,
        ]);
        $safetyComplianceControl->setMetadata([
            'control_level' => 'compliance',
            'legal_requirement' => true,
            'certification_mandatory' => true,
            'audit_trail_required' => true,
            'regulation_standard' => 'ISO45001',
        ]);
        $manager->persist($safetyComplianceControl);
        $this->addReference(self::CONTROL_SAFETY_COMPLIANCE, $safetyComplianceControl);

        // 创建基础模式播放控制 - 管理技能课程
        $managementBasicControl = new CoursePlayControl();
        $managementBasicControl->setCourse($managementCourse);
        $managementBasicControl->setEnabled(true);
        $managementBasicControl->setAllowFastForward(true); // 允许快进
        $managementBasicControl->setAllowSpeedControl(true); // 允许倍速
        $managementBasicControl->setAllowedSpeeds([0.75, 1.0, 1.25, 1.5]); // 标准倍速范围
        $managementBasicControl->setEnableWatermark(false); // 不启用水印
        $managementBasicControl->setWatermarkText(null);
        $managementBasicControl->setWatermarkPosition('bottom-right');
        $managementBasicControl->setWatermarkOpacity(50);
        $managementBasicControl->setMaxDeviceCount(10); // 宽松的设备限制
        $managementBasicControl->setPlayAuthDuration(86400); // 24小时播放凭证
        $managementBasicControl->setEnableResume(true);
        $managementBasicControl->setMinWatchDuration(30); // 最少观看30秒
        $managementBasicControl->setProgressCheckInterval(120); // 每2分钟检查
        $managementBasicControl->setAllowSeeking(true); // 允许拖拽
        $managementBasicControl->setAllowContextMenu(true); // 允许右键菜单
        $managementBasicControl->setAllowDownload(false); // 仍然禁止下载
        $managementBasicControl->setExtendedConfig([
            'casual_learning' => true,
            'social_features' => true,
            'offline_mode' => false,
            'quality_options' => ['360p', '720p', '1080p'],
        ]);
        $managementBasicControl->setMetadata([
            'control_level' => 'basic',
            'course_type' => 'management',
            'flexible_learning' => true,
            'user_friendly' => true,
        ]);
        $manager->persist($managementBasicControl);
        $this->addReference(self::CONTROL_MANAGEMENT_BASIC, $managementBasicControl);

        $manager->flush();
    }

    /**
     * 获取课程引用，如果不存在则跳过
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
            $course->setDescription('用于 CoursePlayControlFixtures 测试的示例课程');
            $course->setValid(true);

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
        return ['course', 'playcontrol', 'security', 'test'];
    }
}
