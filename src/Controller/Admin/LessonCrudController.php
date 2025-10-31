<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Lesson;

/**
 * @extends AbstractCrudController<Lesson>
 */
#[AdminCrud(
    routePath: '/train-course/lesson',
    routeName: 'train_course_lesson'
)]
final class LessonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lesson::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课时')
            ->setEntityLabelInPlural('课时管理')
            ->setPageTitle(Crud::PAGE_INDEX, '课时列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建课时')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑课时')
            ->setPageTitle(Crud::PAGE_DETAIL, '课时详情')
            ->setDefaultSort(['sortNumber' => 'DESC', 'id' => 'ASC'])
            ->setSearchFields(['title', 'uniqueCode'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('chapter', '所属章节')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, Lesson $entity) {
                return $entity->getChapter()->getTitle();
            })
        ;

        yield TextField::new('title', '课时名称')
            ->setRequired(true)
            ->setMaxLength(120)
            ->setHelp('课时的名称，在同一章节中应唯一')
        ;

        yield TextField::new('uniqueCode', '课时编码')
            ->hideOnIndex()
            ->setHelp('课时的唯一编码，用于系统识别')
        ;

        yield ImageField::new('coverThumb', '课时封面')
            ->setBasePath('uploads/lessons')
            ->setUploadDir('public/uploads/lessons')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->hideOnIndex()
            ->setHelp('课时的封面图片')
        ;

        yield UrlField::new('videoUrl', '视频地址')
            ->hideOnIndex()
            ->setHelp('课时对应的视频文件地址')
        ;

        yield IntegerField::new('durationSecond', '视频时长(秒)')
            ->setHelp('视频的总时长，单位为秒')
            ->formatValue(function ($value, Lesson $entity) {
                $seconds = $entity->getDurationSecond() ?? 0;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $seconds = $seconds % 60;

                return sprintf('%02d:%02d:%02d (%d秒)', $hours, $minutes, $seconds, $entity->getDurationSecond() ?? 0);
            })
        ;

        yield IntegerField::new('faceDetectDuration', '人脸识别间隔(秒)')
            ->hideOnIndex()
            ->setHelp('人脸识别检测的间隔时间，单位为秒，建议900秒(15分钟)')
        ;

        yield IntegerField::new('sortNumber', '排序号')
            ->setHelp('数字越大排序越靠前')
        ;

        if (Crud::PAGE_DETAIL === $pageName || Crud::PAGE_INDEX === $pageName) {
            yield NumberField::new('lessonTime', '学时')
                ->hideOnForm()
                ->setNumDecimals(2)
                ->formatValue(function ($value, Lesson $entity) {
                    return number_format($entity->getLessonTime(), 2) . ' 学时';
                })
                ->setHelp('学时按照45分钟一节计算')
            ;
        }

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setTimezone('Asia/Shanghai')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
            ->setTimezone('Asia/Shanghai')
        ;

        yield TextField::new('createUser', '创建者')
            ->onlyOnDetail()
        ;

        yield TextField::new('updateUser', '更新者')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('chapter'))
            ->add('title')
            ->add(NumericFilter::new('durationSecond'))
        ;
    }
}
