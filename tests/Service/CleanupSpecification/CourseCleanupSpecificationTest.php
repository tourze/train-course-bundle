<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CleanupSpecification;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCourseBundle\Service\CleanupSpecification\CourseCleanupSpecification;
use Tourze\TrainCourseBundle\Service\CourseConfigService;

/**
 * @internal
 */
#[CoversClass(CourseCleanupSpecification::class)]
final class CourseCleanupSpecificationTest extends TestCase
{
    private CourseCleanupSpecification $specification;

    private CourseConfigService $configService;

    protected function setUp(): void
    {
        $this->configService = $this->createMock(CourseConfigService::class);
        $this->specification = new CourseCleanupSpecification($this->configService);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseCleanupSpecification::class, $this->specification);
    }

    public function testShouldCleanupCourseReturnsTrueForInvalidCourse(): void
    {
        $this->configService->method('get')
            ->willReturn(7)
        ;

        $course = $this->createInvalidCourse();

        $result = $this->specification->shouldCleanupCourse($course);

        $this->assertTrue($result);
    }

    public function testShouldCleanupCourseReturnsFalseForValidCourse(): void
    {
        $this->configService->method('get')
            ->willReturn(7)
        ;

        $course = $this->createValidCourse();

        $result = $this->specification->shouldCleanupCourse($course);

        $this->assertFalse($result);
    }

    public function testShouldCleanupCourseReturnsFalseForCourseWithEngagement(): void
    {
        $this->configService->method('get')
            ->willReturn(7)
        ;

        $course = $this->createCourseWithEngagement();

        $result = $this->specification->shouldCleanupCourse($course);

        $this->assertFalse($result);
    }

    public function testShouldCleanupCourseReturnsFalseForRecentCourse(): void
    {
        $this->configService->method('get')
            ->willReturn(7)
        ;

        $course = $this->createRecentCourse();

        $result = $this->specification->shouldCleanupCourse($course);

        $this->assertFalse($result);
    }

    public function testShouldCleanupCourseHandlesMissingMethods(): void
    {
        $this->configService->method('get')
            ->willReturn(7)
        ;

        $course = new \stdClass();

        $result = $this->specification->shouldCleanupCourse($course);

        $this->assertTrue($result);
    }

    public function testShouldCleanupCourseUsesDefaultGracePeriod(): void
    {
        $this->configService->method('get')
            ->willReturn('invalid') // 非整数类型
        ;

        $course = $this->createInvalidCourse();

        $result = $this->specification->shouldCleanupCourse($course);

        $this->assertTrue($result);
    }

    /**
     * 创建无效的课程
     */
    private function createInvalidCourse(): object
    {
        return new class {
            public function isValid(): bool
            {
                return false;
            }

            /**
             * @return array<object>
             */
            public function getCollects(): array
            {
                return [];
            }

            /**
             * @return array<object>
             */
            public function getEvaluates(): array
            {
                return [];
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-30 days');
            }
        };
    }

    /**
     * 创建有效的课程
     */
    private function createValidCourse(): object
    {
        return new class {
            public function isValid(): bool
            {
                return true;
            }

            /**
             * @return array<object>
             */
            public function getCollects(): array
            {
                return [];
            }

            /**
             * @return array<object>
             */
            public function getEvaluates(): array
            {
                return [];
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-30 days');
            }
        };
    }

    /**
     * 创建有参与度的课程
     */
    private function createCourseWithEngagement(): object
    {
        return new class {
            public function isValid(): bool
            {
                return false;
            }

            /**
             * @return array<object>
             */
            public function getCollects(): array
            {
                return [new \stdClass()];
            }

            /**
             * @return array<object>
             */
            public function getEvaluates(): array
            {
                return [];
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-30 days');
            }
        };
    }

    /**
     * 创建最近的课程
     */
    private function createRecentCourse(): object
    {
        return new class {
            public function isValid(): bool
            {
                return false;
            }

            /**
             * @return array<object>
             */
            public function getCollects(): array
            {
                return [];
            }

            /**
             * @return array<object>
             */
            public function getEvaluates(): array
            {
                return [];
            }

            public function getCreateTime(): \DateTime
            {
                return new \DateTime('-1 day');
            }
        };
    }
}
