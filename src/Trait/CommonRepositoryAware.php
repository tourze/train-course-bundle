<?php

namespace Tourze\TrainCourseBundle\Trait;

use Doctrine\ORM\EntityManagerInterface;

/**
 * 通用仓库功能 Trait
 */
trait CommonRepositoryAware
{
    /**
     * 保存实体
     */
    public function save(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        
        if ((bool) $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除实体
     */
    public function remove(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        
        if ((bool) $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 批量保存
     */
    public function saveAll(array $entities, bool $flush = true): void
    {
        foreach ($entities as $entity) {
            $this->getEntityManager()->persist($entity);
        }
        
        if ((bool) $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 刷新实体管理器
     */
    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * 清空实体管理器
     */
    public function clear(): void
    {
        $this->getEntityManager()->clear();
    }

    /**
     * 获取实体管理器
     */
    abstract public function getEntityManager(): EntityManagerInterface;
} 