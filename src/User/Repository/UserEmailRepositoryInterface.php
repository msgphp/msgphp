<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\UserEmail;
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

    public function find(string $email): UserEmail;

    public function exists(string $email): bool;

    public function save(UserEmail $userEmail): void;

    public function delete(UserEmail $userEmail): void;
}
