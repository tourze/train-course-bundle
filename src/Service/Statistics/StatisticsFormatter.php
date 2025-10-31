<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\Statistics;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 统计数据格式化器
 */
class StatisticsFormatter
{
    /**
     * 格式化字节大小
     */
    public function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes > 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * (int) $pow));

        return round($bytes, 2) . ' ' . $units[(int) $pow];
    }

    /**
     * 格式化标签
     */
    public function formatLabel(string $key): string
    {
        $replaced = str_replace('_', ' ', $key);

        return ucfirst($replaced);
    }

    /**
     * 格式化指标值
     */
    public function formatMetricValue(mixed $value): string
    {
        if (is_array($value)) {
            $encoded = json_encode($value);

            return false === $encoded ? '' : $encoded;
        }
        if (false === $value) {
            return '';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * 构建CSV数据
     * @param array<string, mixed> $data
     * @return array<int, array<int, string>>
     */
    public function buildCsvData(array $data): array
    {
        $csv = [['类型', '指标', '数值']];
        $csv = array_merge($csv, $this->buildBasicCsvRows($data));

        return array_merge($csv, $this->buildCourseCsvRows($data));
    }

    /**
     * 构建基础统计CSV行
     * @param array<string, mixed> $data
     * @return array<int, array<int, string>>
     */
    private function buildBasicCsvRows(array $data): array
    {
        $rows = [];
        if (!isset($data['basic']) || !is_array($data['basic'])) {
            return $rows;
        }

        foreach ($data['basic'] as $key => $value) {
            $keyStr = is_string($key) ? $key : (string) $key;
            $valueStr = is_scalar($value) ? (string) $value : '';
            $rows[] = ['基础统计', $keyStr, $valueStr];
        }

        return $rows;
    }

    /**
     * 构建课程统计CSV行
     * @param array<string, mixed> $data
     * @return array<int, array<int, string>>
     */
    private function buildCourseCsvRows(array $data): array
    {
        $rows = [];
        if (!isset($data['courses']) || !is_array($data['courses'])) {
            return $rows;
        }

        $coursesByStatus = $data['courses']['by_status'] ?? null;

        if (!is_array($coursesByStatus)) {
            return $rows;
        }

        foreach ($coursesByStatus as $status => $count) {
            $statusStr = is_string($status) ? $status : (string) $status;
            $countStr = is_scalar($count) ? (string) $count : '';
            $rows[] = ['课程统计', $statusStr, $countStr];
        }

        return $rows;
    }
}
