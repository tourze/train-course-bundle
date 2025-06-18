<?php

namespace Tourze\TrainCourseBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 课时学习状态枚举
 */
enum LessonLearnStatus: string
 implements Itemable, Labelable, Selectable{
    
    use ItemTrait;
    use SelectTrait;
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