<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\Entity\EntityCollectionInterface;
use MsgPhp\User\Entity\PendingUser;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface PendingUserRepositoryInterface
{
    /**
     * @return EntityCollectionInterface|PendingUser[]
     */
    public function findAll(int $offset = null, int $limit = null): EntityCollectionInterface;

    public function find(string $email): PendingUser;

    public function findByToken(string $token): PendingUser;

    public function exists(string $email): bool;

    /**
     * @internal
     */
    public function save(PendingUser $user): void;

    /**
     * @internal
     */
    public function delete(PendingUser $user): void;
}
