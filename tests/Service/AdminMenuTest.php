<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\TrainCourseBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private ItemInterface $menuItem;

    protected function onSetUp(): void
    {
        $this->adminMenu = self::getService(AdminMenu::class);
        $menuFactory = new MenuFactory();
        $this->menuItem = $menuFactory->createItem('root');
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testInvokeMethodCreatesMenuItems(): void
    {
        // 调用__invoke方法
        ($this->adminMenu)($this->menuItem);

        // 验证菜单项被创建
        $children = $this->menuItem->getChildren();
        $this->assertNotEmpty($children, '应该创建了菜单项');

        // 验证主菜单存在
        $this->assertArrayHasKey('培训课程管理', $children);
        $trainCourseMenu = $children['培训课程管理'];

        // 验证主菜单有子菜单
        $subMenus = $trainCourseMenu->getChildren();
        $this->assertNotEmpty($subMenus, '主菜单应该有子菜单');
    }

    public function testMenuStructureIsCorrect(): void
    {
        ($this->adminMenu)($this->menuItem);

        $children = $this->menuItem->getChildren();
        $trainCourseMenu = $children['培训课程管理'];
        $subMenus = $trainCourseMenu->getChildren();

        // 验证子菜单的名称
        $expectedSubMenus = ['课程管理', '内容管理', '播放控制', '用户互动', '审核管理'];

        foreach ($expectedSubMenus as $expected) {
            $this->assertArrayHasKey($expected, $subMenus, "应该包含子菜单: {$expected}");
        }
    }

    public function testCourseManagementMenuHasItems(): void
    {
        ($this->adminMenu)($this->menuItem);

        $children = $this->menuItem->getChildren();
        $trainCourseMenu = $children['培训课程管理'];
        $courseMenu = $trainCourseMenu->getChildren()['课程管理'];

        $courseMenuItems = $courseMenu->getChildren();

        // 验证课程管理子菜单项
        $expectedItems = ['课程列表', '课程大纲', '课程版本'];

        foreach ($expectedItems as $expected) {
            $this->assertArrayHasKey($expected, $courseMenuItems, "课程管理应该包含: {$expected}");
        }
    }
}
