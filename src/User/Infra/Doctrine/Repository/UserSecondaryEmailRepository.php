<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\UserSecondaryEmail;
use MsgPhp\User\Repository\UserSecondaryEmailRepositoryInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserSecondaryEmailRepository implements UserSecondaryEmailRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    private $alias = 'user_secondary_email';

    /**
     * @return DomainCollectionInterface|UserSecondaryEmail[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAllByFields(['user' => $userId], $offset, $limit);
    }

    public function find(UserIdInterface $userId, string $email): UserSecondaryEmail
    {
        return $this->doFind(...func_get_args());
    }

    public function findPendingPrimary(UserIdInterface $userId): UserSecondaryEmail
    {
        return $this->doFindByFields(['user' => $userId, 'pendingPrimary' => true]);
    }

    public function findByEmail(string $email): UserSecondaryEmail
    {
        return $this->doFindByFields(['email' => $email]);
    }

    public function findByToken(string $token): UserSecondaryEmail
    {
        return $this->doFindByFields(['token' => $token]);
    }

    public function exists(UserIdInterface $userId, string $email): bool
    {
        return $this->doExists(...func_get_args());
    }

    public function save(UserSecondaryEmail $userSecondaryEmail): void
    {
        $this->doSave($userSecondaryEmail);
    }

    public function delete(UserSecondaryEmail $userSecondaryEmail): void
    {
        $this->doDelete($userSecondaryEmail);
    }
}
