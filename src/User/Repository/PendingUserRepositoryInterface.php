<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\PendingUser;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface PendingUserRepositoryInterface
{
    /**
     * @return DomainCollectionInterface|PendingUser[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollectionInterface;

    public function find(string $email): PendingUser;

    public function findByToken(string $token): PendingUser;

    public function exists(string $email): bool;

    public function save(PendingUser $user): void;

    public function delete(PendingUser $user): void;
}
