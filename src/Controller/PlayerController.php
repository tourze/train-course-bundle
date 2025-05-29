<?php

namespace Tourze\TrainCourseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainCourseBundle\Repository\LessonRepository;
use Tourze\TrainCourseBundle\Service\CourseService;
use WeuiBundle\Service\NoticeService;

#[Route('/job-training/player')]
class PlayerController extends AbstractController
{
    public function __construct(
        private readonly LessonRepository $lessonRepository,
        private readonly CourseService $courseService,
        private readonly NoticeService $noticeService,
    ) {
    }

    #[Route('/video', name: 'job-training-player-video')]
    public function genChance(Request $request): Response
    {
        $lesson = $this->lessonRepository->findOneBy([
            'id' => $request->query->get('lessonId'),
        ]);
        if ($lesson) {
            $url = $this->courseService->getLessonPlayUrl($lesson);
            if (empty($url)) {
                return $this->noticeService->weuiError('找不到视频播放地址');
            }

            return $this->redirect($url);
        }

        return $this->noticeService->weuiError('找不到课程信息');
    }
}
