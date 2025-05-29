<?php

namespace Tourze\TrainCourseBundle\Enum;

/**
 * 课时学习状态枚举
 */
enum LessonLearnStatus: string
{
    case NOT_BUY = 'not_buy';      // 未购买
    case PENDING = 'pending';      // 待学习
    case LEARNING = 'learning';    // 学习中
    case FINISHED = 'finished';    // 已完成

    public function getLabel(): string
    {
        return match ($this) {
            self::NOT_BUY => '未购买',
            self::PENDING => '待学习',
            self::LEARNING => '学习中',
            self::FINISHED => '已完成',
        };
    }
} 