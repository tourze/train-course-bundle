<?php

namespace Tourze\TrainCourseBundle\Service;

use Carbon\CarbonImmutable;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tourze\AliyunVodBundle\Service\VideoManageService;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Lesson;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainRecordBundle\Service\LearnProgressService;

/**
 * 课程服务
 * 负责课程相关的业务逻辑处理
 */
#[WithMonologChannel(channel: 'train_course')]
readonly class CourseService
{
    public function __construct(
        private LoggerInterface $logger,
        private CacheInterface $cache,
        private VideoManageService $videoManageService,
        private CourseConfigService $configService,
        private CourseRepository $courseRepository,
        private ?LearnProgressService $learnProgressService = null,
    ) {
    }

    /**
     * 根据ID查找课程
     */
    public function findById(string $id): ?Course
    {
        return $this->courseRepository->find($id);
    }

    /**
     * 根据条件查找单个课程
     */
    /**
     * @param array<string, mixed> $criteria
     * @param array<string, 'ASC'|'DESC'>|null $orderBy
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?Course
    {
        return $this->courseRepository->findOneBy($criteria, $orderBy);
    }

    /**
     * 根据条件查找多个课程
     *
     * @return Course[]
     */
    /**
     * @param array<string, mixed> $criteria
     * @param array<string, 'ASC'|'DESC'>|null $orderBy
     * @return Course[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->courseRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * 获取所有下级目录
     *
     * @return array|Catalog[]
     */
    /**
     * @return array<int, Catalog>
     */
    public function getAllChildCategories(Catalog $category): array
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
        $videoUrl = $lesson->getVideoUrl();

        if (null === $videoUrl) {
            return '';
        }

        if (str_starts_with($videoUrl, 'ali://')) {
            return $this->getAliVideoPlayUrl($videoUrl);
        }

        if (str_starts_with($videoUrl, 'polyv://')) {
            return $this->getPolyvVideoPlayUrl($videoUrl);
        }

        return $videoUrl;
    }

    private function getAliVideoPlayUrl(string $videoUrl): string
    {
        $vid = trim(str_replace('ali://', '', $videoUrl));

        return $this->cache->get("ali_video_playUrl_{$vid}", function (ItemInterface $item) use ($vid) {
            $item->expiresAt(CarbonImmutable::now()->addMinutes($this->configService->getVideoPlayUrlCacheTime()));

            return $this->fetchAliVideoPlayUrl($vid);
        });
    }

    private function fetchAliVideoPlayUrl(string $vid): string
    {
        try {
            $playInfo = $this->videoManageService->getPlayInfo($vid);

            if (isset($playInfo['playInfoList']) && is_array($playInfo['playInfoList']) && count($playInfo['playInfoList']) > 0) {
                $firstPlayInfo = $playInfo['playInfoList'][0];
                if (is_array($firstPlayInfo) && isset($firstPlayInfo['playURL']) && is_string($firstPlayInfo['playURL'])) {
                    return $firstPlayInfo['playURL'];
                }
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
    }

    private function getPolyvVideoPlayUrl(string $videoUrl): string
    {
        $polyvConfig = $this->configService->getPolyvProxyConfig();

        return str_replace($polyvConfig['prefix'], $polyvConfig['proxy_url'], $videoUrl);
    }

    /**
     * @return array<string, mixed>
     */
    public function getLessonArray(Lesson $lesson): array
    {
        return $lesson->retrieveApiArray();
    }

    /**
     * 检查课程是否有效
     */
    public function isCourseValid(Course $course): bool
    {
        return true === $course->isValid();
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
            if (str_starts_with($videoUrl, $protocol)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取课程学习进度
     * @return array<string, mixed>
     */
    public function getCourseProgress(Course $course, string $userId): array
    {
        // 获取总课时数和总时长
        $totalLessons = $this->getCourseTotalLessons($course);
        $totalDuration = $this->getCourseTotalDuration($course);

        // 如果LearnProgressService可用，使用真实的学习进度数据
        if (null !== $this->learnProgressService) {
            try {
                $progressData = $this->learnProgressService->calculateCourseProgress($userId, (string) $course->getId());
                return [
                    'course_id' => $course->getId(),
                    'user_id' => $userId,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $progressData['completedLessons'],
                    'progress_percentage' => $progressData['overallProgress'],
                    'total_duration' => $totalDuration,
                    'watched_duration' => $progressData['totalEffectiveTime'],
                    'average_progress' => $progressData['averageProgress'],
                    'total_lessons_with_progress' => $progressData['totalLessons'],
                ];
            } catch (\Exception $e) {
                $this->logger->warning('获取学习进度失败，使用默认值', [
                    'userId' => $userId,
                    'courseId' => $course->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 降级方案：返回默认的学习进度数据
        return [
            'course_id' => $course->getId(),
            'user_id' => $userId,
            'total_lessons' => $totalLessons,
            'completed_lessons' => 0,
            'progress_percentage' => 0,
            'total_duration' => $totalDuration,
            'watched_duration' => 0,
            'average_progress' => 0,
            'total_lessons_with_progress' => 0,
        ];
    }
}
