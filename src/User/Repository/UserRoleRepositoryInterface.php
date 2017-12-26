<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\Entity\EntityCollectionInterface;
use MsgPhp\User\Entity\UserRole;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserRoleRepositoryInterface
{
    /**
     * @return EntityCollectionInterface|UserRole[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = null, int $limit = null): EntityCollectionInterface;

    public function find(UserIdInterface $userId, string $role): UserRole;

    public function exists(UserIdInterface $userId, string $role): bool;

    /**
     * @internal
     */
    public function save(UserRole $userRole): void;

    /**
     * @internal
     */
    public function delete(UserRole $userRole): void;
}
