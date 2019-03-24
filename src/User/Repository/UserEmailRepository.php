<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\UserEmail;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserEmailRepository
{
    /**
     * @return DomainCollection|UserEmail[]
     */
    public function findAllByUserId(UserId $userId, int $offset = 0, int $limit = 0): DomainCollection;

    public function find(string $email): UserEmail;

    public function exists(string $email): bool;

    public function save(UserEmail $userEmail): void;

    public function delete(UserEmail $userEmail): void;
}
