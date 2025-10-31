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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TrainCourseBundle\Entity\CoursePlayControl;

/**
 * @extends AbstractCrudController<CoursePlayControl>
 */
#[AdminCrud(routePath: '/train-course/course-play-control', routeName: 'train_course_course_play_control')]
final class CoursePlayControlCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CoursePlayControl::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程播放控制')
            ->setEntityLabelInPlural('课程播放控制管理')
            ->setPageTitle('index', '播放控制列表')
            ->setPageTitle('new', '新建播放控制')
            ->setPageTitle('edit', '编辑播放控制')
            ->setPageTitle('detail', '播放控制详情')
            ->setHelp('index', '管理课程播放控制策略，包括防快进、倍速控制、水印设置等')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['watermarkText'])
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
            ->setHelp('选择需要设置播放控制的课程')
        ;

        yield BooleanField::new('enabled', '启用控制')
            ->setFormTypeOption('attr', ['checked' => 'checked'])
            ->setHelp('是否启用播放控制功能')
        ;

        yield BooleanField::new('allowFastForward', '允许快进')
            ->setHelp('是否允许用户快进播放')
        ;

        yield BooleanField::new('allowSpeedControl', '允许倍速')
            ->setHelp('是否允许用户调整播放倍速')
        ;

        yield BooleanField::new('allowSeeking', '允许拖拽')
            ->setHelp('是否允许用户拖拽进度条')
        ;

        yield BooleanField::new('allowContextMenu', '启用右键菜单')
            ->setHelp('是否启用播放器右键菜单')
        ;

        yield BooleanField::new('allowDownload', '允许下载')
            ->setHelp('是否允许用户下载视频')
        ;

        yield BooleanField::new('enableWatermark', '启用水印')
            ->setFormTypeOption('attr', ['checked' => 'checked'])
            ->setHelp('是否在视频上显示水印')
        ;

        yield TextField::new('watermarkText', '水印文本')
            ->setMaxLength(200)
            ->setHelp('显示在视频上的水印文字')
        ;

        yield ChoiceField::new('watermarkPosition', '水印位置')
            ->setChoices([
                '左上角' => 'top-left',
                '右上角' => 'top-right',
                '左下角' => 'bottom-left',
                '右下角' => 'bottom-right',
                '居中' => 'center',
            ])
            ->setHelp('水印在视频中的显示位置')
        ;

        yield IntegerField::new('watermarkOpacity', '水印透明度')
            ->setHelp('水印透明度（0-100）')
        ;

        yield IntegerField::new('maxDeviceCount', '最大设备数')
            ->setRequired(true)
            ->setHelp('同一账号最多可在多少台设备上播放')
        ;

        yield IntegerField::new('playAuthDuration', '播放凭证有效期（秒）')
            ->setRequired(true)
            ->setHelp('播放授权凭证的有效时长')
        ;

        yield BooleanField::new('enableResume', '启用续播')
            ->setFormTypeOption('attr', ['checked' => 'checked'])
            ->setHelp('是否支持从上次停止位置继续播放')
        ;

        yield IntegerField::new('minWatchDuration', '最小观看时长（秒）')
            ->setHelp('用户至少需要观看多长时间才算有效学习')
        ;

        yield IntegerField::new('progressCheckInterval', '进度检查间隔（秒）')
            ->setHelp('多长时间检查一次播放进度')
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
            ->add(BooleanFilter::new('enabled', '启用控制'))
            ->add(BooleanFilter::new('allowFastForward', '允许快进'))
            ->add(BooleanFilter::new('allowSpeedControl', '允许倍速'))
            ->add(BooleanFilter::new('allowSeeking', '允许拖拽'))
            ->add(BooleanFilter::new('enableWatermark', '启用水印'))
            ->add(TextFilter::new('watermarkText', '水印文本'))
            ->add(NumericFilter::new('maxDeviceCount', '最大设备数'))
            ->add(BooleanFilter::new('enableResume', '启用续播'))
            ->add(NumericFilter::new('minWatchDuration', '最小观看时长'))
            ->add(TextFilter::new('createdBy', '创建人'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
