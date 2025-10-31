<?php

namespace Tourze\TrainCourseBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseAudit;

/**
 * 课程审核数据填充
 * 创建各种审核状态的示例数据，包括待审核、已通过、已拒绝等状态
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class CourseAuditFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 审核引用常量 - 用于其他 Fixture 类引用
    public const AUDIT_PHP_APPROVED = 'audit-php-approved';
    public const AUDIT_SYMFONY_PENDING = 'audit-symfony-pending';
    public const AUDIT_DOCKER_REJECTED = 'audit-docker-rejected';
    public const AUDIT_SAFETY_CONTENT = 'audit-safety-content';
    public const AUDIT_MANAGEMENT_QUALITY = 'audit-management-quality';

    public function load(ObjectManager $manager): void
    {
        // 获取课程引用
        $phpCourse = $this->getCourseReference('php-course');
        $symfonyAdvanced = $this->getCourseReference('symfony-advanced');
        $dockerCourse = $this->getCourseReference('docker-course');
        $safetyCourse = $this->getCourseReference('safety-course');
        $managementCourse = $this->getCourseReference('management-course');

        // 创建已通过的内容审核
        $phpApprovedAudit = new CourseAudit();
        $phpApprovedAudit->setCourse($phpCourse);
        $phpApprovedAudit->setStatus('approved');
        $phpApprovedAudit->setAuditType('content');
        $phpApprovedAudit->setAuditor('审核员001');
        $phpApprovedAudit->setAuditComment('课程内容结构清晰，知识点覆盖全面，审核通过。');
        $phpApprovedAudit->setAuditTime(new \DateTimeImmutable('-2 days'));
        $phpApprovedAudit->setAuditLevel(1);
        $phpApprovedAudit->setPriority(5);
        $phpApprovedAudit->setDeadline(new \DateTimeImmutable('-3 days'));
        $phpApprovedAudit->setAuditData([
            'content_score' => 95,
            'structure_score' => 90,
            'completeness_score' => 92,
            'reviewer_notes' => '整体质量较高，建议作为推荐课程',
        ]);
        $phpApprovedAudit->setMetadata([
            'review_duration_minutes' => 45,
            'total_issues_found' => 2,
            'issues_resolved' => 2,
            'final_score' => 92,
        ]);
        $manager->persist($phpApprovedAudit);
        $this->addReference(self::AUDIT_PHP_APPROVED, $phpApprovedAudit);

        // 创建待审核的课程
        $symfonyPendingAudit = new CourseAudit();
        $symfonyPendingAudit->setCourse($symfonyAdvanced);
        $symfonyPendingAudit->setStatus('pending');
        $symfonyPendingAudit->setAuditType('content');
        $symfonyPendingAudit->setAuditor('审核员002');
        $symfonyPendingAudit->setAuditComment(null); // 待审核，暂无意见
        $symfonyPendingAudit->setAuditTime(null);
        $symfonyPendingAudit->setAuditLevel(1);
        $symfonyPendingAudit->setPriority(8);
        $symfonyPendingAudit->setDeadline(new \DateTimeImmutable('+2 days'));
        $symfonyPendingAudit->setAuditData([
            'submit_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'estimated_review_time' => 60,
            'complexity_level' => 'high',
        ]);
        $symfonyPendingAudit->setMetadata([
            'submitter' => 'course_creator_002',
            'submission_notes' => '高级Symfony开发课程，请重点关注代码示例的准确性',
            'auto_checks_passed' => true,
        ]);
        $manager->persist($symfonyPendingAudit);
        $this->addReference(self::AUDIT_SYMFONY_PENDING, $symfonyPendingAudit);

        // 创建被拒绝的审核记录
        $dockerRejectedAudit = new CourseAudit();
        $dockerRejectedAudit->setCourse($dockerCourse);
        $dockerRejectedAudit->setStatus('rejected');
        $dockerRejectedAudit->setAuditType('quality');
        $dockerRejectedAudit->setAuditor('审核员003');
        $dockerRejectedAudit->setAuditComment('课程内容存在以下问题：1. 部分技术细节过时；2. 缺少实际操作演示；3. 课程结构需要重新组织。请修改后重新提交。');
        $dockerRejectedAudit->setAuditTime(new \DateTimeImmutable('-1 day'));
        $dockerRejectedAudit->setAuditLevel(2);
        $dockerRejectedAudit->setPriority(6);
        $dockerRejectedAudit->setDeadline(new \DateTimeImmutable('-1 day'));
        $dockerRejectedAudit->setAuditData([
            'content_score' => 65,
            'technical_accuracy' => 70,
            'practical_value' => 60,
            'issues_found' => [
                'outdated_docker_version',
                'missing_hands_on_examples',
                'poor_content_structure',
            ],
        ]);
        $dockerRejectedAudit->setMetadata([
            'review_duration_minutes' => 90,
            'revision_required' => true,
            'resubmit_deadline' => (new \DateTime('+7 days'))->format('Y-m-d H:i:s'),
            'priority_issues' => 3,
        ]);
        $manager->persist($dockerRejectedAudit);
        $this->addReference(self::AUDIT_DOCKER_REJECTED, $dockerRejectedAudit);

        // 创建内容审核记录
        $safetyContentAudit = new CourseAudit();
        $safetyContentAudit->setCourse($safetyCourse);
        $safetyContentAudit->setStatus('approved');
        $safetyContentAudit->setAuditType('content');
        $safetyContentAudit->setAuditor('安全审核专家');
        $safetyContentAudit->setAuditComment('企业安全培训内容符合最新法规要求，知识点准确，实用性强。');
        $safetyContentAudit->setAuditTime(new \DateTimeImmutable('-5 hours'));
        $safetyContentAudit->setAuditLevel(1);
        $safetyContentAudit->setPriority(10); // 安全类课程高优先级
        $safetyContentAudit->setDeadline(new \DateTimeImmutable('-6 hours'));
        $safetyContentAudit->setAuditData([
            'compliance_check' => 'passed',
            'regulation_version' => '2024.1',
            'content_score' => 98,
            'safety_standards' => ['ISO45001', 'GB/T28001'],
        ]);
        $safetyContentAudit->setMetadata([
            'specialist_review' => 'safety_expert_001',
            'compliance_verified' => true,
            'legal_review_required' => false,
            'emergency_procedures_included' => true,
        ]);
        $manager->persist($safetyContentAudit);
        $this->addReference(self::AUDIT_SAFETY_CONTENT, $safetyContentAudit);

        // 创建质量审核记录
        $managementQualityAudit = new CourseAudit();
        $managementQualityAudit->setCourse($managementCourse);
        $managementQualityAudit->setStatus('approved');
        $managementQualityAudit->setAuditType('quality');
        $managementQualityAudit->setAuditor('高级审核员');
        $managementQualityAudit->setAuditComment('管理类课程整体质量优秀，理论与实践结合得当，案例丰富且具有代表性。');
        $managementQualityAudit->setAuditTime(new \DateTimeImmutable('-3 hours'));
        $managementQualityAudit->setAuditLevel(2);
        $managementQualityAudit->setPriority(7);
        $managementQualityAudit->setDeadline(new \DateTimeImmutable('-4 hours'));
        $managementQualityAudit->setAuditData([
            'pedagogy_score' => 94,
            'case_studies_quality' => 96,
            'practical_application' => 90,
            'theoretical_foundation' => 92,
            'innovation_level' => 85,
        ]);
        $managementQualityAudit->setMetadata([
            'educational_expert_review' => true,
            'industry_expert_consultation' => true,
            'benchmark_comparison' => 'above_average',
            'recommended_for_promotion' => true,
        ]);
        $manager->persist($managementQualityAudit);
        $this->addReference(self::AUDIT_MANAGEMENT_QUALITY, $managementQualityAudit);

        // 创建一些边界情况的审核记录

        // 取消的审核
        $cancelledAudit = new CourseAudit();
        $cancelledAudit->setCourse($phpCourse);
        $cancelledAudit->setStatus('cancelled');
        $cancelledAudit->setAuditType('update');
        $cancelledAudit->setAuditor('审核员004');
        $cancelledAudit->setAuditComment('课程作者主动撤回审核申请');
        $cancelledAudit->setAuditTime(new \DateTimeImmutable('-1 hour'));
        $cancelledAudit->setAuditLevel(1);
        $cancelledAudit->setPriority(3);
        $cancelledAudit->setAuditData(['cancellation_reason' => 'author_request']);
        $cancelledAudit->setMetadata(['cancelled_by' => 'course_author']);
        $manager->persist($cancelledAudit);

        // 超时的审核（用于测试超时检查功能）
        $overdueAudit = new CourseAudit();
        $overdueAudit->setCourse($dockerCourse);
        $overdueAudit->setStatus('pending');
        $overdueAudit->setAuditType('final');
        $overdueAudit->setAuditor('审核员005');
        $overdueAudit->setAuditComment(null);
        $overdueAudit->setAuditTime(null);
        $overdueAudit->setAuditLevel(3);
        $overdueAudit->setPriority(9);
        $overdueAudit->setDeadline(new \DateTimeImmutable('-1 day')); // 已超时
        $overdueAudit->setAuditData([
            'expected_completion' => (new \DateTime('-1 day'))->format('Y-m-d H:i:s'),
            'complexity' => 'very_high',
        ]);
        $overdueAudit->setMetadata([
            'overdue' => true,
            'escalation_required' => true,
        ]);
        $manager->persist($overdueAudit);

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
            $course->setDescription('用于 CourseAuditFixtures 测试的示例课程');
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
        return ['course', 'audit', 'workflow', 'test'];
    }
}
