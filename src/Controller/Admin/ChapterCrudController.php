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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\TrainCourseBundle\Entity\Chapter;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @extends AbstractCrudController<Chapter>
 */
#[AdminCrud(
    routePath: '/train-course/chapter',
    routeName: 'train_course_chapter'
)]
final class ChapterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chapter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程章节')
            ->setEntityLabelInPlural('课程章节管理')
            ->setPageTitle(Crud::PAGE_INDEX, '章节列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建章节')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑章节')
            ->setPageTitle(Crud::PAGE_DETAIL, '章节详情')
            ->setDefaultSort(['sortNumber' => 'DESC', 'id' => 'ASC'])
            ->setSearchFields(['title', 'uniqueCode'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('course', '所属课程')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, Chapter $entity) {
                return $entity->getCourse()->getTitle();
            })
        ;

        yield TextField::new('title', '章节标题')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setHelp('章节的标题，在同一课程中应唯一')
        ;

        yield TextField::new('uniqueCode', '章节编码')
            ->hideOnIndex()
            ->setHelp('章节的唯一编码，用于系统识别')
        ;

        yield IntegerField::new('sortNumber', '排序号')
            ->setHelp('数字越大排序越靠前')
        ;

        if (Crud::PAGE_DETAIL === $pageName || Crud::PAGE_INDEX === $pageName) {
            yield IntegerField::new('lessonCount', '课时数量')
                ->hideOnForm()
                ->formatValue(function ($value, Chapter $entity) {
                    return $entity->getLessonCount();
                })
            ;

            yield IntegerField::new('lessonTime', '总学时')
                ->hideOnForm()
                ->formatValue(function ($value, Chapter $entity) {
                    return $entity->getLessonTime() . ' 学时';
                })
            ;

            yield IntegerField::new('durationSecond', '总时长')
                ->hideOnForm()
                ->formatValue(function ($value, Chapter $entity) {
                    $seconds = $entity->getDurationSecond();
                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    $seconds = $seconds % 60;

                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                })
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
            ->add(EntityFilter::new('course'))
            ->add('title')
        ;
    }
}
