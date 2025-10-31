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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @extends AbstractCrudController<Course>
 */
#[AdminCrud(routePath: '/train-course/course', routeName: 'train_course_course')]
final class CourseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程')
            ->setEntityLabelInPlural('课程管理')
            ->setPageTitle('index', '课程列表')
            ->setPageTitle('new', '新建课程')
            ->setPageTitle('edit', '编辑课程')
            ->setPageTitle('detail', '课程详情')
            ->setHelp('index', '管理培训课程信息，包括课程内容、价格、讲师等')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['title', 'teacherName', 'description'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('code', '课程编号')
            ->setMaxLength(50)
            ->setHelp('系统自动生成的唯一课程编号')
            ->hideOnForm()
        ;

        yield BooleanField::new('valid', '有效状态')
            ->setFormTypeOption('attr', ['checked' => 'checked'])
            ->setHelp('是否为有效可用的课程')
        ;

        yield AssociationField::new('category', '课程分类')
            ->setRequired(true)
            ->setHelp('选择课程所属的分类')
        ;

        yield TextField::new('title', '课程标题')
            ->setMaxLength(120)
            ->setRequired(true)
            ->setHelp('课程的标题名称')
        ;

        yield TextField::new('teacherName', '任课老师')
            ->setMaxLength(30)
            ->setHelp('授课教师姓名')
        ;

        yield UrlField::new('coverThumb', '课程封面')
            ->setHelp('课程封面图片URL地址')
        ;

        yield TextareaField::new('description', '描述')
            ->setNumOfRows(5)
            ->setHelp('课程的详细介绍内容')
        ;

        yield IntegerField::new('validDay', '有效期（天）')
            ->setRequired(true)
            ->setHelp('课程购买后的有效期，单位：天')
        ;

        yield IntegerField::new('learnHour', '毕业学时')
            ->setHelp('完成课程所需的学时数')
        ;

        yield MoneyField::new('price', '课程价格')
            ->setCurrency('CNY')
            ->setHelp('课程的销售价格')
        ;

        yield IntegerField::new('sortNumber', '排序号')
            ->setHelp('用于排序显示的数字，数值越大越靠前')
            ->hideOnIndex()
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

        // 仅在详情页显示的关联信息
        yield AssociationField::new('chapters', '课程章节')
            ->hideOnForm()
            ->onlyOnDetail()
            ->setHelp('该课程包含的章节列表')
        ;

        yield AssociationField::new('outlines', '课程大纲')
            ->hideOnForm()
            ->onlyOnDetail()
            ->setHelp('该课程的大纲信息')
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
            ->add(TextFilter::new('title', '课程标题'))
            ->add(TextFilter::new('teacherName', '任课老师'))
            ->add(BooleanFilter::new('valid', '有效状态'))
            ->add(EntityFilter::new('category', '课程分类'))
            ->add(NumericFilter::new('validDay', '有效期'))
            ->add(NumericFilter::new('learnHour', '毕业学时'))
            ->add(NumericFilter::new('price', '课程价格'))
            ->add(TextFilter::new('createdBy', '创建人'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
