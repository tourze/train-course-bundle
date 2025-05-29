<?php

namespace Tourze\TrainCourseBundle\Tests\Factory;

use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * 课程实体测试数据工厂
 */
class CourseFactory
{
    /**
     * 创建基础课程实例
     */
    public static function create(array $data = []): Course
    {
        $course = new Course();
        
        $defaults = [
            'title' => '安全生产培训课程',
            'description' => '这是一门关于安全生产的培训课程，内容丰富，讲解清晰。',
            'teacherName' => '张老师',
            'validDay' => 365,
            'learnHour' => 40,
            'coverThumb' => '/uploads/course/cover.jpg',
            'price' => '99.99',
            'valid' => true,
            'createdBy' => 'admin',
            'updatedBy' => 'admin',
        ];
        
        $data = array_merge($defaults, $data);
        
        $course->setTitle($data['title']);
        $course->setDescription($data['description']);
        $course->setTeacherName($data['teacherName']);
        $course->setValidDay($data['validDay']);
        $course->setLearnHour($data['learnHour']);
        $course->setCoverThumb($data['coverThumb']);
        $course->setPrice($data['price']);
        $course->setValid($data['valid']);
        $course->setCreatedBy($data['createdBy']);
        $course->setUpdatedBy($data['updatedBy']);
        
        return $course;
    }

    /**
     * 创建带分类的课程
     */
    public static function createWithCategory(array $data = []): Course
    {
        $course = self::create($data);
        
        $category = new Category();
        $category->setName($data['categoryName'] ?? '安全培训');
        
        $course->setCategory($category);
        
        return $course;
    }

    /**
     * 创建带章节的课程
     */
    public static function createWithChapters(int $chapterCount = 3, array $data = []): Course
    {
        $course = self::create($data);
        
        for ($i = 1; $i <= $chapterCount; $i++) {
            $chapter = new Chapter();
            $chapter->setTitle("第{$i}章 安全基础知识");
            $chapter->setDescription("第{$i}章的详细描述");
            $chapter->setSortNumber($i);
            
            $course->addChapter($chapter);
        }
        
        return $course;
    }

    /**
     * 创建带课时的完整课程
     */
    public static function createWithLessons(int $chapterCount = 2, int $lessonsPerChapter = 3, array $data = []): Course
    {
        $course = self::create($data);
        
        for ($i = 1; $i <= $chapterCount; $i++) {
            $chapter = new Chapter();
            $chapter->setTitle("第{$i}章 安全基础知识");
            $chapter->setDescription("第{$i}章的详细描述");
            $chapter->setSortNumber($i);
            
            for ($j = 1; $j <= $lessonsPerChapter; $j++) {
                $lesson = new Lesson();
                $lesson->setTitle("第{$i}.{$j}节 具体知识点");
                $lesson->setDescription("第{$i}.{$j}节的详细描述");
                $lesson->setVideoUrl("https://example.com/video/{$i}_{$j}.mp4");
                $lesson->setDurationSecond(1800); // 30分钟
                $lesson->setSortNumber($j);
                
                $chapter->addLesson($lesson);
            }
            
            $course->addChapter($chapter);
        }
        
        return $course;
    }

    /**
     * 创建带收藏的课程
     */
    public static function createWithCollects(int $collectCount = 5, array $data = []): Course
    {
        $course = self::create($data);
        
        for ($i = 1; $i <= $collectCount; $i++) {
            $collect = new Collect();
            $collect->setUserId("user{$i}");
            $collect->setStatus('active');
            $collect->setCollectGroup($i % 2 === 0 ? '我的收藏' : '重要课程');
            $collect->setNote("用户{$i}的收藏备注");
            $collect->setSortNumber($i);
            $collect->setIsTop($i <= 2); // 前两个置顶
            
            $course->addCollect($collect);
        }
        
        return $course;
    }

    /**
     * 创建带评价的课程
     */
    public static function createWithEvaluates(int $evaluateCount = 10, array $data = []): Course
    {
        $course = self::create($data);
        
        $ratings = [5, 4, 5, 3, 4, 5, 2, 4, 5, 4]; // 平均4.1分
        
        for ($i = 1; $i <= $evaluateCount; $i++) {
            $evaluate = new Evaluate();
            $evaluate->setUserId("user{$i}");
            $evaluate->setRating($ratings[($i - 1) % count($ratings)]);
            $evaluate->setContent("这是用户{$i}的评价内容，课程很不错！");
            $evaluate->setStatus('published');
            $evaluate->setIsAnonymous($i % 3 === 0); // 每3个匿名一个
            $evaluate->setUserNickname("用户{$i}");
            $evaluate->setUserAvatar("/avatar/user{$i}.jpg");
            $evaluate->setLikeCount(rand(0, 20));
            $evaluate->setReplyCount(rand(0, 5));
            
            $course->addEvaluate($evaluate);
        }
        
        return $course;
    }

    /**
     * 创建完整的课程（包含所有关联数据）
     */
    public static function createComplete(array $data = []): Course
    {
        $course = self::createWithLessons(3, 4, $data); // 3章，每章4节
        
        // 添加分类
        $category = new Category();
        $category->setName($data['categoryName'] ?? '安全培训');
        $course->setCategory($category);
        
        // 添加收藏
        for ($i = 1; $i <= 8; $i++) {
            $collect = new Collect();
            $collect->setUserId("user{$i}");
            $collect->setStatus('active');
            $collect->setCollectGroup($i % 2 === 0 ? '我的收藏' : '重要课程');
            $collect->setNote("用户{$i}的收藏备注");
            $collect->setSortNumber($i);
            $collect->setIsTop($i <= 2);
            
            $course->addCollect($collect);
        }
        
        // 添加评价
        $ratings = [5, 4, 5, 3, 4, 5, 2, 4, 5, 4, 3, 5];
        for ($i = 1; $i <= 12; $i++) {
            $evaluate = new Evaluate();
            $evaluate->setUserId("user{$i}");
            $evaluate->setRating($ratings[($i - 1) % count($ratings)]);
            $evaluate->setContent("这是用户{$i}的评价内容，课程质量很高！");
            $evaluate->setStatus($i <= 10 ? 'published' : 'pending'); // 前10个已发布
            $evaluate->setIsAnonymous($i % 4 === 0);
            $evaluate->setUserNickname("用户{$i}");
            $evaluate->setUserAvatar("/avatar/user{$i}.jpg");
            $evaluate->setLikeCount(rand(0, 25));
            $evaluate->setReplyCount(rand(0, 8));
            
            $course->addEvaluate($evaluate);
        }
        
        return $course;
    }

    /**
     * 创建多个课程
     */
    public static function createMultiple(int $count = 5, array $baseData = []): array
    {
        $courses = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $data = array_merge($baseData, [
                'title' => "安全生产培训课程 第{$i}期",
                'description' => "这是第{$i}期安全生产培训课程",
                'teacherName' => $i % 2 === 0 ? '张老师' : '李老师',
                'validDay' => 365 + ($i * 30),
                'learnHour' => 40 + ($i * 8),
                'price' => (99.99 + ($i * 10)) . '',
            ]);
            
            $courses[] = self::create($data);
        }
        
        return $courses;
    }

    /**
     * 创建不同状态的课程
     */
    public static function createWithDifferentStatuses(): array
    {
        return [
            'valid' => self::create(['title' => '有效课程', 'valid' => true]),
            'invalid' => self::create(['title' => '无效课程', 'valid' => false]),
            'free' => self::create(['title' => '免费课程', 'price' => '0.00']),
            'premium' => self::create(['title' => '高级课程', 'price' => '299.99']),
            'short' => self::create(['title' => '短期课程', 'validDay' => 30, 'learnHour' => 8]),
            'long' => self::create(['title' => '长期课程', 'validDay' => 730, 'learnHour' => 120]),
        ];
    }

    /**
     * 创建用于搜索测试的课程
     */
    public static function createForSearch(): array
    {
        return [
            self::create([
                'title' => '安全生产基础培训',
                'description' => '企业安全生产基础知识培训课程',
                'teacherName' => '安全专家张老师',
            ]),
            self::create([
                'title' => '消防安全知识',
                'description' => '消防安全知识和应急处理培训',
                'teacherName' => '消防专家李老师',
            ]),
            self::create([
                'title' => '职业健康培训',
                'description' => '职业健康和劳动保护相关培训',
                'teacherName' => '健康专家王老师',
            ]),
            self::create([
                'title' => '化工安全管理',
                'description' => '化工企业安全管理制度和操作规范',
                'teacherName' => '化工专家赵老师',
            ]),
        ];
    }
} 