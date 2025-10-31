<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Repository\CourseRepository;
use Tourze\TrainCourseBundle\Service\CourseAnalyticsService;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsCollector;
use Tourze\TrainCourseBundle\Service\Statistics\StatisticsOutputRenderer;

/**
 * 课程统计命令
 *
 * 生成课程相关的统计报告，包括课程数量、评价统计、收藏统计等
 */
#[AsCommand(name: self::NAME, description: '生成课程统计报告')]
class CourseStatisticsCommand extends Command
{
    public const NAME = 'train-course:statistics';

    public function __construct(
        private readonly CourseRepository $courseRepository,
        private readonly CourseAnalyticsService $analyticsService,
        private readonly StatisticsCollector $statisticsCollector,
        private readonly StatisticsOutputRenderer $outputRenderer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, '输出格式 (table|json|csv)', 'table')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, '输出文件路径')
            ->addOption('detailed', null, InputOption::VALUE_NONE, '显示详细统计信息')
            ->addOption('course-id', null, InputOption::VALUE_OPTIONAL, '指定课程ID获取详细统计')
            ->addOption('top', null, InputOption::VALUE_OPTIONAL, '显示排行榜前N名', 10)
            ->setHelp(<<<'TXT'
                该命令用于生成课程统计报告：

                <info>基础统计：</info>
                  <comment>php bin/console train-course:statistics</comment>

                <info>详细统计：</info>
                  <comment>php bin/console train-course:statistics --detailed</comment>

                <info>指定课程统计：</info>
                  <comment>php bin/console train-course:statistics --course-id=123</comment>

                <info>JSON格式输出：</info>
                  <comment>php bin/console train-course:statistics --format=json</comment>

                <info>输出到文件：</info>
                  <comment>php bin/console train-course:statistics --output=/path/to/report.json --format=json</comment>

                <info>排行榜：</info>
                  <comment>php bin/console train-course:statistics --top=20</comment>
                TXT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $options = $this->parseInputOptions($input);

        if ($this->shouldShowSingleCourseReport($options)) {
            return $this->showCourseStatistics($io, (string) $options['courseId'], $options['format'], $options['outputFile']);
        }

        return $this->generateStatisticsReport($io, $options);
    }

    /**
     * 生成统计报告
     * @param array{format: string, outputFile: string|null, detailed: bool, courseId: string|null, topCount: int} $options
     */
    private function generateStatisticsReport(SymfonyStyle $io, array $options): int
    {
        $io->title('课程统计报告');

        $statistics = $this->statisticsCollector->collectStatistics($options['detailed'], $options['topCount']);
        $this->outputRenderer->outputStatistics($io, $statistics, $options['format'], $options['outputFile'], $options['detailed']);

        $io->success('统计报告生成完成');

        return Command::SUCCESS;
    }

    /**
     * 显示指定课程的统计信息
     */
    private function showCourseStatistics(SymfonyStyle $io, string $courseId, string $format, ?string $outputFile): int
    {
        $course = $this->courseRepository->find($courseId);
        if (null === $course) {
            $io->error(sprintf('课程 ID %s 不存在', $courseId));

            return Command::FAILURE;
        }

        $report = $this->analyticsService->getCourseAnalyticsReport($course);

        switch ($format) {
            case 'json':
                $this->outputRenderer->outputStatistics($io, $report, 'json', $outputFile, false);
                break;
            default:
                $this->outputRenderer->displayCourseReport($io, $course, $report);
                break;
        }

        return Command::SUCCESS;
    }

    /**
     * 解析输入选项
     * @return array{format: string, outputFile: string|null, detailed: bool, courseId: string|null, topCount: int}
     */
    private function parseInputOptions(InputInterface $input): array
    {
        $formatOption = $input->getOption('format');
        $format = is_string($formatOption) ? $formatOption : 'table';

        $outputFile = $input->getOption('output');
        $outputFile = is_string($outputFile) ? $outputFile : null;

        $detailed = (bool) $input->getOption('detailed');

        $courseId = $input->getOption('course-id');
        $courseId = is_string($courseId) ? $courseId : null;

        $topOption = $input->getOption('top');
        $topCount = is_numeric($topOption) ? (int) $topOption : 10;

        return [
            'format' => $format,
            'outputFile' => $outputFile,
            'detailed' => $detailed,
            'courseId' => $courseId,
            'topCount' => $topCount,
        ];
    }

    /**
     * 判断是否显示单个课程报告
     * @param array{format: string, outputFile: string|null, detailed: bool, courseId: string|null, topCount: int} $options
     */
    private function shouldShowSingleCourseReport(array $options): bool
    {
        return null !== $options['courseId'];
    }
}
