<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\Statistics;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 统计数据输出渲染器
 */
class StatisticsOutputRenderer
{
    public function __construct(
        private readonly StatisticsFormatter $formatter,
        private readonly StatisticsTableRenderer $tableRenderer,
    ) {
    }

    /**
     * 输出统计数据
     * @param array<string, mixed> $statistics
     */
    public function outputStatistics(SymfonyStyle $io, array $statistics, string $format, ?string $outputFile, bool $detailed): void
    {
        match ($format) {
            'json' => $this->outputJson($io, $statistics, $outputFile),
            'csv' => $this->outputCsv($io, $statistics, $outputFile),
            default => $this->tableRenderer->outputTable($io, $statistics, $detailed),
        };
    }

    /**
     * JSON格式输出
     * @param array<string, mixed> $data
     */
    private function outputJson(SymfonyStyle $io, array $data, ?string $outputFile): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (false === $json) {
            $json = '{}';
        }

        if (null !== $outputFile) {
            file_put_contents($outputFile, $json);
            $io->success(sprintf('统计报告已保存到: %s', $outputFile));
        } else {
            $io->writeln($json);
        }
    }

    /**
     * CSV格式输出
     * @param array<string, mixed> $data
     */
    private function outputCsv(SymfonyStyle $io, array $data, ?string $outputFile): void
    {
        $csv = $this->formatter->buildCsvData($data);

        if (null !== $outputFile) {
            $this->saveCsvToFile($csv, $outputFile, $io);

            return;
        }

        $this->displayCsvToConsole($csv, $io);
    }

    /**
     * 保存CSV数据到文件
     * @param array<int, array<int, string>> $csv
     */
    private function saveCsvToFile(array $csv, string $outputFile, SymfonyStyle $io): void
    {
        $fp = fopen($outputFile, 'w');
        if (false === $fp) {
            return;
        }

        foreach ($csv as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        $io->success(sprintf('CSV报告已保存到: %s', $outputFile));
    }

    /**
     * 在控制台显示CSV数据
     * @param array<int, array<int, string>> $csv
     */
    private function displayCsvToConsole(array $csv, SymfonyStyle $io): void
    {
        foreach ($csv as $row) {
            $io->writeln(implode(',', $row));
        }
    }

    /**
     * 显示课程详细报告
     * @param array<string, mixed> $report
     */
    public function displayCourseReport(SymfonyStyle $io, Course $course, array $report): void
    {
        $io->title(sprintf('课程分析报告: %s', $course->getTitle()));

        $this->tableRenderer->renderCourseBasicInfo($io, $report);
        $this->tableRenderer->renderPopularityMetrics($io, $report);
        $this->tableRenderer->renderRecommendationsIfAvailable($io, $report);
    }
}
