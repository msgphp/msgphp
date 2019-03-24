<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface RoleRepository
{
    /**
     * @return DomainCollection|Role[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollection;

    public function find(string $name): Role;

    public function exists(string $name): bool;

    public function save(Role $role): void;

    public function delete(Role $role): void;
}
