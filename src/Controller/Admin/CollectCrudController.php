<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\TrainCourseBundle\Entity\Collect;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @extends AbstractCrudController<Collect>
 */
#[AdminCrud(
    routePath: '/train-course/collect',
    routeName: 'train_course_collect'
)]
final class CollectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Collect::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程收藏')
            ->setEntityLabelInPlural('课程收藏管理')
            ->setPageTitle(Crud::PAGE_INDEX, '收藏列表')
            ->setPageTitle(Crud::PAGE_NEW, '添加收藏')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑收藏')
            ->setPageTitle(Crud::PAGE_DETAIL, '收藏详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['userId', 'collectGroup', 'note'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $statusChoices = [
            '已收藏' => 'active',
            '已取消' => 'cancelled',
            '已隐藏' => 'hidden',
        ];

        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('userId', '用户ID')
            ->setRequired(true)
            ->setMaxLength(50)
            ->setHelp('收藏该课程的用户ID')
        ;

        yield AssociationField::new('course', '收藏课程')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, Collect $entity) {
                return $entity->getCourse()?->getTitle() ?? '';
            })
            ->setHelp('被收藏的课程')
        ;

        yield ChoiceField::new('status', '收藏状态')
            ->setChoices($statusChoices)
            ->renderAsBadges([
                'active' => 'success',
                'cancelled' => 'secondary',
                'hidden' => 'warning',
            ])
            ->setHelp('收藏的当前状态')
        ;

        yield TextField::new('collectGroup', '收藏分组')
            ->hideOnIndex()
            ->setMaxLength(100)
            ->setHelp('用户自定义的收藏分组名称')
        ;

        yield TextareaField::new('note', '收藏备注')
            ->hideOnIndex()
            ->setMaxLength(65535)
            ->setHelp('用户对该收藏的备注说明')
        ;

        yield IntegerField::new('sortNumber', '排序号')
            ->hideOnIndex()
            ->setHelp('收藏的排序号，数字越大排序越靠前')
        ;

        yield BooleanField::new('isTop', '是否置顶')
            ->hideOnIndex()
            ->setHelp('是否将该收藏置顶显示')
        ;

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield CodeEditorField::new('metadata', '扩展属性')
                ->setLanguage('javascript')
                ->hideOnIndex()
                ->setHelp('扩展属性，JSON格式存储')
            ;
        } else {
            yield TextField::new('metadata', '扩展属性')
                ->hideOnIndex()
                ->formatValue(function ($value, Collect $entity) {
                    $metadata = $entity->getMetadata();

                    return null !== $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
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
            ->add('userId')
            ->add(EntityFilter::new('course'))
            ->add(ChoiceFilter::new('status', '收藏状态')->setChoices([
                '已收藏' => 'active',
                '已取消' => 'cancelled',
                '已隐藏' => 'hidden',
            ]))
            ->add('collectGroup')
            ->add(BooleanFilter::new('isTop'))
        ;
    }
}
