<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Repository\RoleRepository as BaseRoleRepository;
use MsgPhp\User\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class RoleRepository implements BaseRoleRepository
{
    use DomainEntityRepositoryTrait;

    /**
     * @return DomainCollection|Role[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollection
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
