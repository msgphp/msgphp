<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\Role;
use MsgPhp\User\Repository\RoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class RoleRepository implements RoleRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    private $alias = 'role';

    /**
     * @return DomainCollectionInterface|Role[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAll($offset, $limit);
    }

    public function find(string $name): Role
    {
        return $this->doFind($name);
    }

    public function exists(string $name): bool
    {
        return $this->doExists($name);
    }

    public function save(Role $role): void
    {
        $this->doSave($role);
    }

    public function delete(Role $role): void
    {
        $this->doDelete($role);
    }
}
