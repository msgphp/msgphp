<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\UserId;
use MsgPhp\User\UserRole;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserRoleRepository
{
    /**
     * @return DomainCollection|UserRole[]
     */
    public function findAllByUserId(UserId $userId, int $offset = 0, int $limit = 0): DomainCollection;

    public function find(UserId $userId, string $roleName): UserRole;

    public function exists(UserId $userId, string $roleName): bool;

    public function save(UserRole $userRole): void;

    public function delete(UserRole $userRole): void;
}
