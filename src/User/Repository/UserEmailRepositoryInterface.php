<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\UserEmail;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserEmailRepositoryInterface
{
    /**
     * @return DomainCollectionInterface|UserEmail[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function find(UserIdInterface $userId, string $email): UserEmail;

    public function findByEmail(string $email): UserEmail;

    public function findByConfirmationToken(string $token): UserEmail;

    public function exists(UserIdInterface $userId, string $email): bool;

    public function save(UserEmail $userEmail): void;

    public function delete(UserEmail $userEmail): void;
}
