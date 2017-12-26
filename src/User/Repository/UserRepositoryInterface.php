<?php

declare(strict_types=1);

namespace MsgPhp\User\Repository;

use MsgPhp\Domain\Entity\EntityCollectionInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface UserRepositoryInterface
{
    /**
     * @return EntityCollectionInterface|User[]
     */
    public function findAll(int $offset = null, int $limit = null): EntityCollectionInterface;

    public function find(UserIdInterface $id): User;

    public function findByEmail(string $email): User;

    public function findByPasswordResetToken(string $token): User;

    public function exists(UserIdInterface $id): bool;

    /**
     * @internal
     */
    public function save(User $user): void;

    /**
     * @internal
     */
    public function delete(User $user): void;
}
