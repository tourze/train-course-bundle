<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Service\BackupStrategy;

/**
 * 课程数据保存服务
 */
class CourseDataSaver
{
    /**
     * 保存课程数据到文件
     * @param array<int, array<string, mixed>> $courseData
     */
    public function saveCourseData(string $backupDir, array $courseData, string $filename): string
    {
        $dataFile = $backupDir . '/' . $filename;
        file_put_contents($dataFile, json_encode($courseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $dataFile;
    }

    /**
     * 保存增量备份数据
     * @param array<int, array<string, mixed>> $courseData
     */
    public function saveIncrementalData(string $backupDir, array $courseData): string
    {
        $dataFile = $backupDir . '/incremental_courses.json';
        $data = [
            'since' => date('Y-m-d H:i:s'), // 这里应该传入实际的since参数
            'backup_time' => date('Y-m-d H:i:s'),
            'courses' => $courseData,
        ];
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $dataFile;
    }
}
