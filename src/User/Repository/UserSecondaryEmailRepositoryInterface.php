<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\Entity\EntityCollectionInterface;
use MsgPhp\User\Entity\UserSecondaryEmail;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserSecondaryEmailRepositoryInterface
{
    /**
     * @return EntityCollectionInterface|UserSecondaryEmail[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = null, int $limit = null): EntityCollectionInterface;

    public function find(UserIdInterface $userId, string $email): UserSecondaryEmail;

    public function findPendingPrimary(UserIdInterface $userId): UserSecondaryEmail;

    public function findByEmail(string $email): UserSecondaryEmail;

    public function findByToken(string $token): UserSecondaryEmail;

    public function exists(UserIdInterface $userId, string $email): bool;

    /**
     * @internal
     */
    public function save(UserSecondaryEmail $userSecondaryEmail): void;

    /**
     * @internal
     */
    public function delete(UserSecondaryEmail $userSecondaryEmail): void;
}
