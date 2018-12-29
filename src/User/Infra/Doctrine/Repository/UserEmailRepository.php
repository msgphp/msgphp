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

    /**
     * @return DomainCollectionInterface|UserEmail[]
     */
    public function findAllByUserId(UserIdInterface $userId, int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAllByFields(['user' => $userId], $offset, $limit);
    }

    public function find(string $email): UserEmail
    {
        return $this->doFind($email);
    }

    public function exists(string $email): bool
    {
        return $this->doExists($email);
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
