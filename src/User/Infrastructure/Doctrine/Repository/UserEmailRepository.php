<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Repository;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Repository\UserEmailRepository as BaseUserEmailRepository;
use MsgPhp\User\UserEmail;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserEmailRepository implements BaseUserEmailRepository
{
    use DomainEntityRepositoryTrait;

    /**
     * @return DomainCollection|UserEmail[]
     */
    public function findAllByUserId(UserId $userId, int $offset = 0, int $limit = 0): DomainCollection
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
