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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TrainCourseBundle\Entity\CourseVersion;

/**
 * @extends AbstractCrudController<CourseVersion>
 */
#[AdminCrud(routePath: '/train-course/course-version', routeName: 'train_course_course_version')]
final class CourseVersionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CourseVersion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程版本')
            ->setEntityLabelInPlural('课程版本管理')
            ->setPageTitle('index', '课程版本列表')
            ->setPageTitle('new', '新建课程版本')
            ->setPageTitle('edit', '编辑课程版本')
            ->setPageTitle('detail', '课程版本详情')
            ->setHelp('index', '管理课程版本控制，记录历史变更和版本回滚')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['version', 'title', 'description'])
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
            ->setHelp('选择版本所属的课程')
        ;

        yield TextField::new('version', '版本号')
            ->setMaxLength(50)
            ->setRequired(true)
            ->setHelp('版本号，如：v1.0.0')
        ;

        yield TextField::new('title', '版本标题')
            ->setMaxLength(200)
            ->setHelp('版本的标题描述')
        ;

        yield TextareaField::new('description', '版本描述')
            ->setNumOfRows(4)
            ->setHelp('该版本的详细描述信息')
        ;

        yield TextareaField::new('changeLog', '变更说明')
            ->setNumOfRows(4)
            ->setHelp('本版本的变更内容和更新说明')
        ;

        yield ChoiceField::new('status', '版本状态')
            ->setChoices([
                '草稿' => 'draft',
                '已发布' => 'published',
                '已归档' => 'archived',
                '已废弃' => 'deprecated',
            ])
            ->setRequired(true)
            ->setHelp('当前版本的状态')
        ;

        yield BooleanField::new('isCurrent', '当前版本')
            ->setHelp('是否为当前正在使用的版本')
        ;

        yield DateTimeField::new('publishedAt', '发布时间')
            ->setHelp('版本发布的时间')
        ;

        yield TextField::new('publishedBy', '发布人')
            ->setMaxLength(100)
            ->setHelp('版本发布人员')
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
            ->add(TextFilter::new('version', '版本号'))
            ->add(TextFilter::new('title', '版本标题'))
            ->add(ChoiceFilter::new('status', '版本状态')->setChoices([
                '草稿' => 'draft',
                '已发布' => 'published',
                '已归档' => 'archived',
                '已废弃' => 'deprecated',
            ]))
            ->add(BooleanFilter::new('isCurrent', '当前版本'))
            ->add(TextFilter::new('publishedBy', '发布人'))
            ->add(DateTimeFilter::new('publishedAt', '发布时间'))
            ->add(TextFilter::new('createdBy', '创建人'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
