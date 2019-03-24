<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Repository\UserRoleRepository as BaseUserRoleRepository;
use MsgPhp\User\UserId;
use MsgPhp\User\UserRole;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserRoleRepository implements BaseUserRoleRepository
{
    use DomainEntityRepositoryTrait;

    /**
     * @return DomainCollection|UserRole[]
     */
    public function findAllByUserId(UserId $userId, int $offset = 0, int $limit = 0): DomainCollection
    {
        return $this->doFindAllByFields(['user' => $userId], $offset, $limit);
    }

    public function find(UserId $userId, string $roleName): UserRole
    {
        return $this->doFind(['user' => $userId, 'role' => $roleName]);
    }

    public function exists(UserId $userId, string $roleName): bool
    {
        return $this->doExists(['user' => $userId, 'role' => $roleName]);
    }

    public function save(UserRole $userRole): void
    {
        $this->doSave($userRole);
    }

    public function delete(UserRole $userRole): void
    {
        $this->doDelete($userRole);
    }
}
