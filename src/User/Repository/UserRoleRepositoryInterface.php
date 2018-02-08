<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\UserRole;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserRoleRepositoryInterface
{
    /**
     * @return DomainCollectionInterface|UserRole[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function find(UserIdInterface $userId, string $roleName): UserRole;

    public function exists(UserIdInterface $userId, string $roleName): bool;

    public function save(UserRole $userRole): void;

    public function delete(UserRole $userRole): void;
}
