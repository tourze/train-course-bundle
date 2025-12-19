<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service\CourseContent;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\CourseOutline;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Service\CourseContent\CourseStructureBuilder;
use Tourze\TrainCourseBundle\Tests\Factory\CourseFactory;

/**
 * CourseStructureBuilder 集成测试
 *
 * @internal
 */
#[CoversClass(CourseStructureBuilder::class)]
#[RunTestsInSeparateProcesses]
final class CourseStructureBuilderTest extends AbstractIntegrationTestCase
{
    private CourseStructureBuilder $builder;
    private EntityManagerInterface $em;

    protected function onSetUp(): void
    {
        $this->builder = self::getService(CourseStructureBuilder::class);
        $this->em = self::getService(EntityManagerInterface::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CourseStructureBuilder::class, $this->builder);
    }

    public function testBuildCourseContentStructureReturnsArray(): void
    {
        $course = $this->createPersistedCourse();

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course', $result);
        $this->assertArrayHasKey('chapters', $result);
        $this->assertArrayHasKey('outlines', $result);
        $this->assertArrayHasKey('statistics', $result);
    }

    public function testBuildCourseContentStructureIncludesCourseInfo(): void
    {
        $course = $this->createPersistedCourse('测试课程', '课程描述');

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result['course']);
        $this->assertArrayHasKey('id', $result['course']);
        $this->assertArrayHasKey('title', $result['course']);
        $this->assertArrayHasKey('description', $result['course']);
        $this->assertSame('测试课程', $result['course']['title']);
    }

    public function testBuildCourseContentStructureIncludesChapters(): void
    {
        $course = $this->createPersistedCourseWithChapters(2);

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result['chapters']);
        $this->assertCount(2, $result['chapters']);

        // 验证章节数据结构
        $firstChapter = $result['chapters'][0];
        $this->assertArrayHasKey('id', $firstChapter);
        $this->assertArrayHasKey('title', $firstChapter);
        $this->assertArrayHasKey('sort_number', $firstChapter);
        $this->assertArrayHasKey('lessons', $firstChapter);
    }

    public function testBuildCourseContentStructureIncludesOutlines(): void
    {
        $course = $this->createPersistedCourse();

        // 创建大纲
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setTitle('课程大纲');
        $outline->setSortNumber(1);
        $outline->setStatus('published');
        $this->em->persist($outline);
        $this->em->flush();

        $result = $this->builder->buildCourseContentStructure($course);

        $this->assertIsArray($result['outlines']);
        $this->assertCount(1, $result['outlines']);

        // 验证大纲数据结构
        $firstOutline = $result['outlines'][0];
        $this->assertArrayHasKey('id', $firstOutline);
        $this->assertArrayHasKey('title', $firstOutline);
        $this->assertSame('课程大纲', $firstOutline['title']);
    }

    public function testBuildStatisticsReturnsArray(): void
    {
        $course = $this->createPersistedCourseWithContent();

        $result = $this->builder->buildStatistics($course);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('course_id', $result);
        $this->assertArrayHasKey('course_title', $result);
        $this->assertArrayHasKey('total_chapters', $result);
        $this->assertArrayHasKey('total_lessons', $result);
        $this->assertArrayHasKey('total_outlines', $result);
        $this->assertArrayHasKey('total_duration_seconds', $result);
    }

    public function testBuildStatisticsCalculatesTotals(): void
    {
        $course = $this->createPersistedCourseWithContent();

        $result = $this->builder->buildStatistics($course);

        $this->assertIsInt($result['total_chapters']);
        $this->assertIsInt($result['total_lessons']);
        $this->assertIsInt($result['total_outlines']);
        $this->assertIsInt($result['total_duration_seconds']);
        $this->assertGreaterThanOrEqual(0, $result['total_chapters']);
        $this->assertGreaterThanOrEqual(0, $result['total_lessons']);
    }

    /**
     * 创建并持久化基础课程
     */
    private function createPersistedCourse(string $title = '测试课程', string $description = '课程描述'): Course
    {
        $course = CourseFactory::create(['title' => $title, 'description' => $description]);
        $this->em->persist($course);
        $this->em->flush();

        return $course;
    }

    /**
     * 创建带有章节的课程
     */
    private function createPersistedCourseWithChapters(int $chapterCount = 2): Course
    {
        $course = $this->createPersistedCourse();

        for ($i = 1; $i <= $chapterCount; ++$i) {
            $chapter = new Chapter();
            $chapter->setCourse($course);
            $chapter->setTitle("第{$i}章");
            $chapter->setSortNumber($i);
            $this->em->persist($chapter);
        }

        $this->em->flush();
        $this->em->refresh($course);

        return $course;
    }

    /**
     * 创建包含完整内容的课程
     */
    private function createPersistedCourseWithContent(): Course
    {
        $course = $this->createPersistedCourse();

        // 创建章节和课时
        for ($i = 1; $i <= 2; ++$i) {
            $chapter = new Chapter();
            $chapter->setCourse($course);
            $chapter->setTitle("第{$i}章");
            $chapter->setSortNumber($i);
            $this->em->persist($chapter);

            // 为每章创建课时
            for ($j = 1; $j <= 3; ++$j) {
                $lesson = new Lesson();
                $lesson->setChapter($chapter);
                $lesson->setTitle("第{$i}.{$j}节");
                $lesson->setSortNumber($j);
                $lesson->setDurationSecond(1800); // 30分钟
                $lesson->setVideoUrl('https://example.com/video.mp4');
                $this->em->persist($lesson);
            }
        }

        // 创建大纲
        $outline = new CourseOutline();
        $outline->setCourse($course);
        $outline->setTitle('课程大纲');
        $outline->setSortNumber(1);
        $outline->setStatus('published');
        $this->em->persist($outline);

        $this->em->flush();
        $this->em->refresh($course);

        return $course;
    }
}
