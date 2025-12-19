<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\BackupStrategy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Service\BackupStrategy\CourseSerializer;

/**
 * @internal
 */
#[CoversClass(CourseSerializer::class)]
final class CourseSerializerTest extends TestCase
{
    private CourseSerializer $serializer;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->serializer = new CourseSerializer();
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseSerializer::class, $this->serializer);
    }

    public function testSerializeCoursesWithEmptyArray(): void
    {
        $courses = [];
        $result = $this->serializer->serializeCourses($courses);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testSerializeCoursesWithSingleCourse(): void
    {
        $course = $this->createMockCourse(1, '测试课程');
        $courses = [$course];

        $result = $this->serializer->serializeCourses($courses);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('title', $result[0]);
        $this->assertArrayHasKey('chapters', $result[0]);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('测试课程', $result[0]['title']);
    }

    public function testSerializeCoursesWithMultipleCourses(): void
    {
        $course1 = $this->createMockCourse(1, '课程1');
        $course2 = $this->createMockCourse(2, '课程2');
        $course3 = $this->createMockCourse(3, '课程3');
        $courses = [$course1, $course2, $course3];

        $result = $this->serializer->serializeCourses($courses);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        foreach ($result as $index => $courseData) {
            $this->assertIsArray($courseData);
            $this->assertArrayHasKey('id', $courseData);
            $this->assertArrayHasKey('title', $courseData);
            $this->assertSame($index + 1, $courseData['id']);
        }
    }

    public function testSerializeCoursesWithProgressCreatesProgressBar(): void
    {
        $courses = [
            $this->createMockCourse(1, '课程1'),
            $this->createMockCourse(2, '课程2'),
        ];

        $result = $this->serializer->serializeCoursesWithProgress($courses, $this->io);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testSerializeCourseWithAllFields(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-02 15:30:00');

        $course = $this->createFullMockCourse(
            id: 1,
            title: '完整课程',
            description: '课程描述',
            coverThumb: '/uploads/cover.jpg',
            price: '99.99',
            validDay: 365,
            learnHour: 40,
            teacherName: '张老师',
            instructor: '李教授',
            valid: true,
            createTime: $createTime,
            updateTime: $updateTime
        );

        $courses = [$course];
        $result = $this->serializer->serializeCourses($courses);

        $this->assertCount(1, $result);
        $courseData = $result[0];

        $this->assertSame(1, $courseData['id']);
        $this->assertSame('完整课程', $courseData['title']);
        $this->assertSame('课程描述', $courseData['description']);
        $this->assertSame('/uploads/cover.jpg', $courseData['cover_thumb']);
        $this->assertSame('99.99', $courseData['price']);
        $this->assertSame(365, $courseData['valid_day']);
        $this->assertSame(40, $courseData['learn_hour']);
        $this->assertSame('张老师', $courseData['teacher_name']);
        $this->assertSame('李教授', $courseData['instructor']);
        $this->assertTrue($courseData['valid']);
        $this->assertSame('2024-01-01 10:00:00', $courseData['create_time']);
        $this->assertSame('2024-01-02 15:30:00', $courseData['update_time']);
        $this->assertIsArray($courseData['chapters']);
    }

    public function testSerializeCourseWithMissingMethods(): void
    {
        $course = new \stdClass();
        $courses = [$course];

        $result = $this->serializer->serializeCourses($courses);

        $this->assertCount(1, $result);
        $courseData = $result[0];

        $this->assertNull($courseData['id']);
        $this->assertNull($courseData['title']);
        $this->assertNull($courseData['description']);
        $this->assertNull($courseData['cover_thumb']);
        $this->assertNull($courseData['price']);
        $this->assertNull($courseData['valid_day']);
        $this->assertNull($courseData['learn_hour']);
        $this->assertNull($courseData['teacher_name']);
        $this->assertNull($courseData['instructor']);
        $this->assertFalse($courseData['valid']);
        $this->assertNull($courseData['create_time']);
        $this->assertNull($courseData['update_time']);
    }

    public function testSerializeCourseWithNullValues(): void
    {
        $course = $this->createMockCourseWithNulls();
        $courses = [$course];

        $result = $this->serializer->serializeCourses($courses);

        $this->assertCount(1, $result);
        $courseData = $result[0];

        $this->assertArrayHasKey('title', $courseData);
        $this->assertArrayHasKey('description', $courseData);
        $this->assertNull($courseData['title']);
        $this->assertNull($courseData['description']);
    }

    public function testSerializeCourseWithStringTimestamps(): void
    {
        $course = $this->createMockCourseWithStringTimestamps(
            '2024-01-01 10:00:00',
            '2024-01-02 15:30:00'
        );

        $courses = [$course];
        $result = $this->serializer->serializeCourses($courses);

        $this->assertCount(1, $result);
        $courseData = $result[0];

        $this->assertSame('2024-01-01 10:00:00', $courseData['create_time']);
        $this->assertSame('2024-01-02 15:30:00', $courseData['update_time']);
    }

    public function testSerializeCoursesWithProgressHandlesEmptyArray(): void
    {
        $courses = [];
        $result = $this->serializer->serializeCoursesWithProgress($courses, $this->io);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * 创建简单的Mock课程对象
     */
    private function createMockCourse(int $id, string $title): object
    {
        return new class($id, $title) {
            public function __construct(private readonly int $id, private readonly string $title)
            {
            }

            public function getId(): int
            {
                return $this->id;
            }

            public function getTitle(): string
            {
                return $this->title;
            }

            public function isValid(): bool
            {
                return true;
            }
        };
    }

    /**
     * 创建完整的Mock课程对象
     */
    private function createFullMockCourse(
        int $id,
        string $title,
        string $description,
        string $coverThumb,
        string $price,
        int $validDay,
        int $learnHour,
        string $teacherName,
        string $instructor,
        bool $valid,
        \DateTimeInterface $createTime,
        \DateTimeInterface $updateTime,
    ): object {
        return new class($id, $title, $description, $coverThumb, $price, $validDay, $learnHour, $teacherName, $instructor, $valid, $createTime, $updateTime) {
            public function __construct(
                private readonly int $id,
                private readonly string $title,
                private readonly string $description,
                private readonly string $coverThumb,
                private readonly string $price,
                private readonly int $validDay,
                private readonly int $learnHour,
                private readonly string $teacherName,
                private readonly string $instructor,
                private readonly bool $valid,
                private readonly \DateTimeInterface $createTime,
                private readonly \DateTimeInterface $updateTime,
            ) {
            }

            public function getId(): int
            {
                return $this->id;
            }

            public function getTitle(): string
            {
                return $this->title;
            }

            public function getDescription(): string
            {
                return $this->description;
            }

            public function getCoverThumb(): string
            {
                return $this->coverThumb;
            }

            public function getPrice(): string
            {
                return $this->price;
            }

            public function getValidDay(): int
            {
                return $this->validDay;
            }

            public function getLearnHour(): int
            {
                return $this->learnHour;
            }

            public function getTeacherName(): string
            {
                return $this->teacherName;
            }

            public function getInstructor(): string
            {
                return $this->instructor;
            }

            public function isValid(): bool
            {
                return $this->valid;
            }

            public function getCreateTime(): \DateTimeInterface
            {
                return $this->createTime;
            }

            public function getUpdateTime(): \DateTimeInterface
            {
                return $this->updateTime;
            }
        };
    }

    /**
     * 创建返回null值的Mock课程对象
     */
    private function createMockCourseWithNulls(): object
    {
        return new class {
            public function getId(): int
            {
                return 1;
            }

            public function getTitle(): ?string
            {
                return null;
            }

            public function getDescription(): ?string
            {
                return null;
            }

            public function isValid(): bool
            {
                return false;
            }
        };
    }

    /**
     * 创建使用字符串时间戳的Mock课程对象
     */
    private function createMockCourseWithStringTimestamps(
        string $createTime,
        string $updateTime,
    ): object {
        return new class($createTime, $updateTime) {
            public function __construct(
                private readonly string $createTime,
                private readonly string $updateTime,
            ) {
            }

            public function getId(): int
            {
                return 1;
            }

            public function getTitle(): string
            {
                return 'Test';
            }

            public function isValid(): bool
            {
                return true;
            }

            public function getCreateTime(): string
            {
                return $this->createTime;
            }

            public function getUpdateTime(): string
            {
                return $this->updateTime;
            }
        };
    }
}
