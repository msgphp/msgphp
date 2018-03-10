<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\UserEmail;
use MsgPhp\User\Repository\UserEmailRepositoryInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserEmailRepository implements UserEmailRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    private $alias = 'user_email';

    /**
     * @return DomainCollectionInterface|UserEmail[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAllByFields(['user' => $userId], $offset, $limit);
    }

    public function find(UserIdInterface $userId, string $email): UserEmail
    {
        return $this->doFind(['user' => $userId, 'email' => $email]);
    }

    public function findByEmail(string $email): UserEmail
    {
        return $this->doFindByFields(['email' => $email]);
    }

    public function findByConfirmationToken(string $token): UserEmail
    {
        return $this->doFindByFields(['confirmationToken' => $token]);
    }

    public function exists(UserIdInterface $userId, string $email): bool
    {
        return $this->doExists(['user' => $userId, 'email' => $email]);
    }

    public function save(UserEmail $userEmail): void
    {
        $this->doSave($userEmail);
    }

    public function delete(UserEmail $userEmail): void
    {
        $this->doDelete($userEmail);
    }
}
