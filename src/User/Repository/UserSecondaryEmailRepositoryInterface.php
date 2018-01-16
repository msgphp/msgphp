<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\UserSecondaryEmail;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserSecondaryEmailRepositoryInterface
{
    /**
     * @return DomainCollectionInterface|UserSecondaryEmail[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function find(UserIdInterface $userId, string $email): UserSecondaryEmail;

    public function findPendingPrimary(UserIdInterface $userId): UserSecondaryEmail;

    public function findByEmail(string $email): UserSecondaryEmail;

    public function findByConfirmationToken(string $token): UserSecondaryEmail;

    public function exists(UserIdInterface $userId, string $email): bool;

    public function save(UserSecondaryEmail $userSecondaryEmail): void;

    public function delete(UserSecondaryEmail $userSecondaryEmail): void;
}
