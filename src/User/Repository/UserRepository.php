<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\User;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserRepository
{
    /**
     * @return DomainCollection|User[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollection;

    public function find(UserId $id): User;

    public function findByUsername(string $username): User;

    public function exists(UserId $id): bool;

    public function usernameExists(string $username): bool;

    public function save(User $user): void;

    public function delete(User $user): void;
}
