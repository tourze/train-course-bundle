<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TrainCourseBundle\Entity\CourseOutline;

/**
 * @extends AbstractCrudController<CourseOutline>
 */
#[AdminCrud(routePath: '/train-course/course-outline', routeName: 'train_course_course_outline')]
final class CourseOutlineCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CourseOutline::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程大纲')
            ->setEntityLabelInPlural('课程大纲管理')
            ->setPageTitle('index', '课程大纲列表')
            ->setPageTitle('new', '新建课程大纲')
            ->setPageTitle('edit', '编辑课程大纲')
            ->setPageTitle('detail', '课程大纲详情')
            ->setHelp('index', '管理课程大纲信息，包括学习目标、内容要点、考核标准等')
            ->setDefaultSort(['sortNumber' => 'DESC', 'createTime' => 'DESC'])
            ->setSearchFields(['title', 'learningObjectives', 'contentPoints'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('course', '关联课程')
            ->setRequired(true)
            ->setHelp('选择大纲所属的课程')
        ;

        yield TextField::new('title', '大纲标题')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('课程大纲的标题')
        ;

        yield TextareaField::new('learningObjectives', '学习目标')
            ->setNumOfRows(4)
            ->setHelp('该大纲章节的学习目标和要求')
        ;

        yield TextareaField::new('contentPoints', '内容要点')
            ->setNumOfRows(4)
            ->setHelp('该章节的主要内容要点')
        ;

        yield TextareaField::new('keyDifficulties', '重点难点')
            ->setNumOfRows(3)
            ->setHelp('该章节的重点和难点内容')
        ;

        yield TextareaField::new('assessmentCriteria', '考核标准')
            ->setNumOfRows(3)
            ->setHelp('该章节的考核评价标准')
        ;

        yield TextareaField::new('references', '参考资料')
            ->setNumOfRows(3)
            ->setHelp('相关的参考资料和学习材料')
        ;

        yield IntegerField::new('estimatedMinutes', '预计学习时长（分钟）')
            ->setHelp('预计完成该章节需要的时间')
        ;

        yield IntegerField::new('sortNumber', '排序号')
            ->setRequired(true)
            ->setHelp('用于排序显示的数字，数值越大越靠前')
        ;

        yield ChoiceField::new('status', '状态')
            ->setChoices([
                '草稿' => 'draft',
                '已发布' => 'published',
                '已归档' => 'archived',
            ])
            ->setRequired(true)
            ->setHelp('大纲的发布状态')
        ;

        yield TextField::new('createdBy', '创建人')
            ->hideOnForm()
        ;

        yield TextField::new('updatedBy', '更新人')
            ->hideOnForm()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('course', '关联课程'))
            ->add(TextFilter::new('title', '大纲标题'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '草稿' => 'draft',
                '已发布' => 'published',
                '已归档' => 'archived',
            ]))
            ->add(NumericFilter::new('estimatedMinutes', '预计学习时长'))
            ->add(NumericFilter::new('sortNumber', '排序号'))
            ->add(TextFilter::new('createdBy', '创建人'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
