<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\{User, Username};
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserRepository implements UserRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    private $alias = 'user';

    /**
     * @return DomainCollectionInterface|User[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollectionInterface
    {
        return $this->doFindAll($offset, $limit);
    }

    public function find(UserIdInterface $id): User
    {
        return $this->doFind($id);
    }

    public function findByUsername(string $username): User
    {
        $qb = $this->createUsernameQueryBuilder($username);

        if (null === $qb) {
            return $this->doFindByFields(['username' => $username]);
        }

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw EntityNotFoundException::createForFields($this->class, ['username' => $username]);
        }
    }

    public function exists(UserIdInterface $id): bool
    {
        return $this->doExists($id);
    }

    public function usernameExists(string $username): bool
    {
        $qb = $this->createUsernameQueryBuilder($username);

        if (null === $qb) {
            return $this->doExistsByFields(['username' => $username]);
        }

        $qb->select('1');

        return (bool) $qb->getQuery()->getScalarResult();
    }

    public function save(User $user): void
    {
        $this->doSave($user);
    }

    public function delete(User $user): void
    {
        $this->doDelete($user);
    }

    private function createUsernameQueryBuilder(string $username): ?QueryBuilder
    {
        if ($this->em->getMetadataFactory()->isTransient(Username::class)) {
            return null;
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('user');
        $qb->from($this->class, 'user');
        $qb->join(Username::class, 'username', Join::WITH, 'username.user = user');
        $qb->where($qb->expr()->eq('username.username', ':username'));
        $qb->setMaxResults(1);
        $qb->setParameter('username', $username);

        return $qb;
    }
}
