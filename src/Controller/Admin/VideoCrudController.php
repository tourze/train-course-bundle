<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\AliyunVodBundle\Entity\AliyunVodConfig;
use Tourze\TrainCourseBundle\Entity\Video;

/**
 * @extends AbstractCrudController<Video>
 */
#[AdminCrud(
    routePath: '/train-course/video',
    routeName: 'train_course_video'
)]
final class VideoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Video::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('课程视频')
            ->setEntityLabelInPlural('课程视频管理')
            ->setPageTitle(Crud::PAGE_INDEX, '视频列表')
            ->setPageTitle(Crud::PAGE_NEW, '上传视频')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑视频')
            ->setPageTitle(Crud::PAGE_DETAIL, '视频详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['title', 'videoId'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $statusChoices = [
            '正常' => 'Normal',
            '处理中' => 'Processing',
            '失败' => 'Failed',
            '已删除' => 'Deleted',
        ];

        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('title', '视频标题')
            ->setMaxLength(120)
            ->setHelp('视频的标题名称')
        ;

        yield TextField::new('videoId', '阿里云视频ID')
            ->hideOnIndex()
            ->setMaxLength(64)
            ->setHelp('阿里云VOD系统中的视频ID')
        ;

        yield TextField::new('size', '文件大小')
            ->hideOnIndex()
            ->formatValue(function ($value, Video $entity) {
                $size = $entity->getSize();
                if (null === $size || '' === $size || '0' === $size) {
                    return '-';
                }

                $bytes = (int) $size;
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $i = 0;

                for (; $bytes > 1024 && $i < count($units) - 1; ++$i) {
                    $bytes /= 1024;
                }

                return round($bytes, 2) . ' ' . $units[$i];
            })
            ->setHelp('视频文件的大小')
        ;

        yield TextField::new('duration', '视频时长')
            ->formatValue(function ($value, Video $entity) {
                $duration = $entity->getDuration();
                if (null === $duration || '' === $duration || '0' === $duration) {
                    return '-';
                }

                $seconds = (float) $duration;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $seconds = $seconds % 60;

                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            })
            ->setHelp('视频的播放时长')
        ;

        yield UrlField::new('coverUrl', '视频封面')
            ->hideOnIndex()
            ->setHelp('视频的封面图片URL')
        ;

        yield ChoiceField::new('status', '视频状态')
            ->setChoices($statusChoices)
            ->renderAsBadges([
                'Normal' => 'success',
                'Processing' => 'warning',
                'Failed' => 'danger',
                'Deleted' => 'secondary',
            ])
            ->setHelp('视频在阿里云VOD中的状态')
        ;

        yield AssociationField::new('vodConfig', 'VOD配置')
            ->hideOnIndex()
            ->autocomplete()
            ->formatValue(function ($value, Video $entity) {
                return $entity->getVodConfig()?->getName() ?? '';
            })
            ->setHelp('关联的阿里云VOD配置')
        ;

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
            ->add('title')
            ->add(ChoiceFilter::new('status', '视频状态')->setChoices([
                '正常' => 'Normal',
                '处理中' => 'Processing',
                '失败' => 'Failed',
                '已删除' => 'Deleted',
            ]))
            ->add(EntityFilter::new('vodConfig'))
        ;
    }
}
