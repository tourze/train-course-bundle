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
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\TrainCourseBundle\Entity\Evaluate;

/**
 * @extends AbstractCrudController<Evaluate>
 */
#[AdminCrud(
    routePath: '/train-course/evaluate',
    routeName: 'train_course_evaluate'
)]
final class EvaluateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Evaluate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程评价')
            ->setEntityLabelInPlural('课程评价管理')
            ->setPageTitle(Crud::PAGE_INDEX, '评价列表')
            ->setPageTitle(Crud::PAGE_NEW, '添加评价')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑评价')
            ->setPageTitle(Crud::PAGE_DETAIL, '评价详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['userId', 'content', 'userNickname'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $statusChoices = [
            '已发布' => 'published',
            '待审核' => 'pending',
            '已拒绝' => 'rejected',
            '已隐藏' => 'hidden',
        ];

        $ratingChoices = [
            '很好 (5星)' => 5,
            '较好 (4星)' => 4,
            '一般 (3星)' => 3,
            '较差 (2星)' => 2,
            '很差 (1星)' => 1,
        ];

        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('userId', '用户ID')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('评价该课程的用户ID')
        ;

        yield AssociationField::new('course', '评价课程')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value, Evaluate $entity) {
                return $entity->getCourse()?->getTitle() ?? '';
            })
            ->setHelp('被评价的课程')
        ;

        yield ChoiceField::new('rating', '评分')
            ->setChoices($ratingChoices)
            ->renderAsBadges([
                5 => 'success',
                4 => 'primary',
                3 => 'warning',
                2 => 'danger',
                1 => 'dark',
            ])
            ->formatValue(function ($value, Evaluate $entity) {
                return $entity->getRating() . '星 (' . $entity->getRatingLabel() . ')';
            })
            ->setHelp('用户给出的星级评分')
        ;

        yield TextareaField::new('content', '评价内容')
            ->hideOnIndex()
            ->setMaxLength(65535)
            ->setHelp('用户的评价文字内容')
        ;

        yield ChoiceField::new('status', '评价状态')
            ->setChoices($statusChoices)
            ->renderAsBadges([
                'published' => 'success',
                'pending' => 'warning',
                'rejected' => 'danger',
                'hidden' => 'secondary',
            ])
            ->setHelp('评价的审核状态')
        ;

        yield BooleanField::new('isAnonymous', '是否匿名')
            ->hideOnIndex()
            ->setHelp('用户是否选择匿名评价')
        ;

        yield IntegerField::new('likeCount', '点赞数')
            ->hideOnIndex()
            ->setHelp('该评价获得的点赞数量')
        ;

        yield IntegerField::new('replyCount', '回复数')
            ->hideOnIndex()
            ->setHelp('该评价的回复数量')
        ;

        yield TextField::new('userNickname', '用户昵称')
            ->hideOnIndex()
            ->setMaxLength(100)
            ->setHelp('评价用户的昵称')
        ;

        yield ImageField::new('userAvatar', '用户头像')
            ->setBasePath('/uploads/avatars')
            ->setUploadDir('public/uploads/avatars')
            ->hideOnIndex()
            ->setHelp('评价用户的头像URL')
        ;

        yield DateTimeField::new('auditTime', '审核时间')
            ->hideOnIndex()
            ->setTimezone('Asia/Shanghai')
            ->setHelp('管理员审核评价的时间')
        ;

        yield TextField::new('auditor', '审核人员')
            ->hideOnIndex()
            ->setMaxLength(100)
            ->setHelp('审核该评价的管理员')
        ;

        yield TextareaField::new('auditComment', '审核意见')
            ->hideOnIndex()
            ->setMaxLength(65535)
            ->setHelp('管理员的审核意见或备注')
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
                ->formatValue(function ($value, Evaluate $entity) {
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
            ->add(NumericFilter::new('rating'))
            ->add(ChoiceFilter::new('status', '评价状态')->setChoices([
                '已发布' => 'published',
                '待审核' => 'pending',
                '已拒绝' => 'rejected',
                '已隐藏' => 'hidden',
            ]))
            ->add(BooleanFilter::new('isAnonymous'))
            ->add('userNickname')
            ->add('auditor')
        ;
    }
}
