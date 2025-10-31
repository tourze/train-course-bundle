<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 课程序列化服务
 */
class CourseSerializer
{
    /**
     * 序列化课程列表并显示进度
     * @param array<object> $courses
     * @return array<int, array<string, mixed>>
     */
    public function serializeCoursesWithProgress(array $courses, SymfonyStyle $io): array
    {
        $progressBar = $io->createProgressBar(count($courses));
        $progressBar->start();

        $courseData = [];
        foreach ($courses as $course) {
            $courseData[] = $this->serializeCourse($course);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);

        return $courseData;
    }

    /**
     * 序列化课程列表
     * @param array<object> $courses
     * @return array<int, array<string, mixed>>
     */
    public function serializeCourses(array $courses): array
    {
        return array_values(array_map(
            fn (object $course): array => $this->serializeCourse($course),
            $courses
        ));
    }

    /**
     * 序列化课程数据
     * @return array<string, mixed>
     */
    private function serializeCourse(object $course): array
    {
        $createTime = $this->extractCourseCreateTime($course);
        $updateTime = $this->extractCourseUpdateTime($course);

        return [
            'id' => $this->extractCourseId($course),
            'title' => $this->extractCourseTitle($course),
            'description' => $this->extractCourseDescription($course),
            'cover_thumb' => $this->extractCourseCoverThumb($course),
            'price' => $this->extractCoursePrice($course),
            'valid_day' => $this->extractCourseValidDay($course),
            'learn_hour' => $this->extractCourseLearnHour($course),
            'teacher_name' => $this->extractCourseTeacherName($course),
            'instructor' => $this->extractCourseInstructor($course),
            'valid' => $this->extractCourseValid($course),
            'create_time' => $createTime instanceof \DateTimeInterface ? $createTime->format('Y-m-d H:i:s') : $createTime,
            'update_time' => $updateTime instanceof \DateTimeInterface ? $updateTime->format('Y-m-d H:i:s') : $updateTime,
            'chapters' => [], // 实际实现中应该序列化章节数据
        ];
    }

    private function extractCourseId(object $course): mixed
    {
        return method_exists($course, 'getId') ? $course->getId() : null;
    }

    private function extractCourseTitle(object $course): ?string
    {
        if (!method_exists($course, 'getTitle')) {
            return null;
        }
        $title = $course->getTitle();

        return is_string($title) ? $title : null;
    }

    private function extractCourseDescription(object $course): ?string
    {
        if (!method_exists($course, 'getDescription')) {
            return null;
        }
        $description = $course->getDescription();

        return is_string($description) ? $description : null;
    }

    private function extractCourseCoverThumb(object $course): ?string
    {
        if (!method_exists($course, 'getCoverThumb')) {
            return null;
        }
        $coverThumb = $course->getCoverThumb();

        return is_string($coverThumb) ? $coverThumb : null;
    }

    private function extractCoursePrice(object $course): mixed
    {
        return method_exists($course, 'getPrice') ? $course->getPrice() : null;
    }

    private function extractCourseValidDay(object $course): ?int
    {
        if (!method_exists($course, 'getValidDay')) {
            return null;
        }
        $validDay = $course->getValidDay();

        return is_int($validDay) ? $validDay : null;
    }

    private function extractCourseLearnHour(object $course): ?int
    {
        if (!method_exists($course, 'getLearnHour')) {
            return null;
        }
        $learnHour = $course->getLearnHour();

        return is_int($learnHour) ? $learnHour : null;
    }

    private function extractCourseTeacherName(object $course): ?string
    {
        if (!method_exists($course, 'getTeacherName')) {
            return null;
        }
        $teacherName = $course->getTeacherName();

        return is_string($teacherName) ? $teacherName : null;
    }

    private function extractCourseInstructor(object $course): ?string
    {
        if (!method_exists($course, 'getInstructor')) {
            return null;
        }
        $instructor = $course->getInstructor();

        return is_string($instructor) ? $instructor : null;
    }

    private function extractCourseValid(object $course): bool
    {
        if (!method_exists($course, 'isValid')) {
            return false;
        }
        $valid = $course->isValid();

        return is_bool($valid) ? $valid : false;
    }

    private function extractCourseCreateTime(object $course): \DateTimeInterface|string|null
    {
        if (!method_exists($course, 'getCreateTime')) {
            return null;
        }
        $createTime = $course->getCreateTime();
        if ($createTime instanceof \DateTimeInterface || is_string($createTime)) {
            return $createTime;
        }

        return null;
    }

    private function extractCourseUpdateTime(object $course): \DateTimeInterface|string|null
    {
        if (!method_exists($course, 'getUpdateTime')) {
            return null;
        }
        $updateTime = $course->getUpdateTime();
        if ($updateTime instanceof \DateTimeInterface || is_string($updateTime)) {
            return $updateTime;
        }

        return null;
    }
}
