<?php

namespace Tourze\TrainCourseBundle\Service;

use Carbon\CarbonImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tourze\AliyunVodBundle\Service\VideoManageService;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课程服务
 * 负责课程相关的业务逻辑处理
 */
class CourseService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
        private readonly VideoManageService $videoManageService,
        private readonly CourseConfigService $configService,
    ) {
    }

    /**
     * 获取所有下级目录
     *
     * @return array|Category[]
     */
    public function getAllChildCategories(Category $category): array
    {
        $result = [
            $category,
        ];
        foreach ($category->getChildren() as $child) {
            $result = array_merge($result, $this->getAllChildCategories($child));
        }

        return $result;
    }

    /**
     * 获取课时播放地址
     * 支持多种视频协议：ali://、polyv://、直接URL
     */
    public function getLessonPlayUrl(Lesson $lesson): string
    {
        if (str_starts_with($lesson->getVideoUrl(), 'ali://')) {
            $vid = str_replace('ali://', '', $lesson->getVideoUrl());
            $vid = trim($vid);

            return $this->cache->get("ali_video_playUrl_{$vid}", function (ItemInterface $item) use ($vid) {
                $item->expiresAt(CarbonImmutable::now()->addMinutes($this->configService->getVideoPlayUrlCacheTime()));

                try {
                    $playInfo = $this->videoManageService->getPlayInfo($vid);

                    // 获取第一个播放地址（通常是最高质量的）
                    if (!empty($playInfo['playInfoList'])) {
                        return $playInfo['playInfoList'][0]['playURL'];
                    }

                    $this->logger->warning('未找到视频播放信息', ['vid' => $vid]);
                    return $vid;
                } catch (\Exception $exception) {
                    $this->logger->error('获取阿里视频信息遇到错误', [
                        'vid' => $vid,
                        'exception' => $exception->getMessage(),
                    ]);

                    return $vid;
                }
            });
        }

        if (str_starts_with($lesson->getVideoUrl(), 'polyv://')) {
            $polyvConfig = $this->configService->getPolyvProxyConfig();
            return str_replace($polyvConfig['prefix'], $polyvConfig['proxy_url'], $lesson->getVideoUrl());
        }

        return strval($lesson->getVideoUrl());
    }

    public function getLessonArray(Lesson $lesson): array
    {
        return $lesson->retrieveApiArray();
    }

    /**
     * 检查课程是否有效
     */
    public function isCourseValid(Course $course): bool
    {
        return $course->isValid() === true;
    }

    /**
     * 获取课程总时长（秒）
     */
    public function getCourseTotalDuration(Course $course): int
    {
        $totalDuration = 0;
        foreach ($course->getChapters() as $chapter) {
            $totalDuration += $chapter->getDurationSecond();
        }
        return $totalDuration;
    }

    /**
     * 获取课程总课时数
     */
    public function getCourseTotalLessons(Course $course): int
    {
        $totalLessons = 0;
        foreach ($course->getChapters() as $chapter) {
            $totalLessons += $chapter->getLessonCount();
        }
        return $totalLessons;
    }

    /**
     * 检查视频URL协议是否支持
     */
    public function isSupportedVideoProtocol(string $videoUrl): bool
    {
        $supportedProtocols = $this->configService->getSupportedVideoProtocols();

        foreach ($supportedProtocols as $protocol) {
            if ((bool) str_starts_with($videoUrl, $protocol)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取课程学习进度（需要用户学习记录）
     * 这里只是接口定义，具体实现需要用户学习记录相关的服务
     */
    public function getCourseProgress(Course $course, string $userId): array
    {
        // TODO: 实现用户学习进度计算
        return [
            'course_id' => $course->getId(),
            'user_id' => $userId,
            'total_lessons' => $this->getCourseTotalLessons($course),
            'completed_lessons' => 0,
            'progress_percentage' => 0,
            'total_duration' => $this->getCourseTotalDuration($course),
            'watched_duration' => 0,
        ];
    }
}
