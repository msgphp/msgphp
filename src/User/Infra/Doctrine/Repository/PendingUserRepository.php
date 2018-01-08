<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\PendingUser;
use MsgPhp\User\Repository\PendingUserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PendingUserRepository implements PendingUserRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    private $alias = 'pending_user';
    private $idFields = ['email'];

    /**
     * @return DomainCollectionInterface|PendingUser[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAll($offset, $limit);
    }

    public function find(string $email): PendingUser
    {
        return $this->doFind($email);
    }

    public function findByToken(string $token): PendingUser
    {
        return $this->doFindByFields(['token' => $token]);
    }

    public function exists(string $email): bool
    {
        return $this->doExists($email);
    }

    public function save(PendingUser $user): void
    {
        $this->doSave($user);
    }

    public function delete(PendingUser $user): void
    {
        $this->doDelete($user);
    }
}
