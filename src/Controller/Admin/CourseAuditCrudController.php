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
use Tourze\TrainCourseBundle\Entity\CourseAudit;

/**
 * @extends AbstractCrudController<CourseAudit>
 */
#[AdminCrud(routePath: '/train-course/course-audit', routeName: 'train_course_course_audit')]
final class CourseAuditCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CourseAudit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程审核')
            ->setEntityLabelInPlural('课程审核管理')
            ->setPageTitle('index', '课程审核列表')
            ->setPageTitle('new', '新建课程审核')
            ->setPageTitle('edit', '编辑课程审核')
            ->setPageTitle('detail', '课程审核详情')
            ->setHelp('index', '管理课程审核流程，包括审核状态、意见和历史记录')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['auditor', 'auditComment'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('course', '课程名称')
            ->setRequired(true)
            ->setHelp('选择需要审核的课程')
        ;

        yield ChoiceField::new('status', '审核状态')
            ->setChoices([
                '待审核' => 'pending',
                '已通过' => 'approved',
                '已拒绝' => 'rejected',
                '已取消' => 'cancelled',
            ])
            ->setRequired(true)
            ->setHelp('当前审核状态')
        ;

        yield ChoiceField::new('auditType', '审核类型')
            ->setChoices([
                '内容审核' => 'content',
                '质量审核' => 'quality',
                '终审' => 'final',
                '更新审核' => 'update',
            ])
            ->setRequired(true)
            ->setHelp('审核的类型分类')
        ;

        yield TextField::new('auditor', '审核人员')
            ->setMaxLength(100)
            ->setHelp('负责审核的人员姓名')
        ;

        yield TextareaField::new('auditComment', '审核意见')
            ->setNumOfRows(4)
            ->setHelp('审核的详细意见和建议')
        ;

        yield DateTimeField::new('auditTime', '审核时间')
            ->setHelp('完成审核的时间')
        ;

        yield IntegerField::new('auditLevel', '审核级别')
            ->setRequired(true)
            ->setHelp('审核的级别，数字越大级别越高')
        ;

        yield IntegerField::new('priority', '优先级')
            ->setHelp('审核优先级，数字越大优先级越高')
        ;

        yield DateTimeField::new('deadline', '截止时间')
            ->setHelp('审核的截止时间')
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
            ->add(EntityFilter::new('course', '课程名称'))
            ->add(ChoiceFilter::new('status', '审核状态')->setChoices([
                '待审核' => 'pending',
                '已通过' => 'approved',
                '已拒绝' => 'rejected',
                '已取消' => 'cancelled',
            ]))
            ->add(ChoiceFilter::new('auditType', '审核类型')->setChoices([
                '内容审核' => 'content',
                '质量审核' => 'quality',
                '终审' => 'final',
                '更新审核' => 'update',
            ]))
            ->add(TextFilter::new('auditor', '审核人员'))
            ->add(NumericFilter::new('auditLevel', '审核级别'))
            ->add(NumericFilter::new('priority', '优先级'))
            ->add(DateTimeFilter::new('auditTime', '审核时间'))
            ->add(DateTimeFilter::new('deadline', '截止时间'))
            ->add(TextFilter::new('createdBy', '创建人'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
