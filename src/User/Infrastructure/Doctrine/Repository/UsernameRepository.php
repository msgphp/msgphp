<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Infrastructure\Doctrine\UsernameLookup;
use MsgPhp\User\Repository\UsernameRepository as BaseUsernameRepository;
use MsgPhp\User\Username;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @template T of Username
 * @implements BaseUsernameRepository<T>
 */
final class UsernameRepository implements BaseUsernameRepository
{
    /** @use DomainEntityRepositoryTrait<T> */
    use DomainEntityRepositoryTrait;

    /** @var UsernameLookup<T> */
    private $lookup;

    /**
     * @param class-string<T>   $class
     * @param UsernameLookup<T> $lookup
     */
    public function __construct(string $class, EntityManagerInterface $em, UsernameLookup $lookup)
    {
        $this->class = $class;
        $this->em = $em;
        $this->lookup = $lookup;
    }

    public function lookup(): DomainCollection
    {
        return new GenericDomainCollection($this->lookup->lookup());
    }

    public function findAll(int $offset = 0, int $limit = 0): DomainCollection
    {
        $qb = $this->createQueryBuilder();
        $qb->indexBy($this->getAlias(), 'username');

        /** @var DomainCollection<string, T> */
        return $this->createResultSet($qb->getQuery(), $offset, $limit);
    }

    public function find(string $username): Username
    {
        return $this->doFind($username);
    }

    public function exists(string $username): bool
    {
        return $this->doExists($username);
    }

    public function save(Username $username): void
    {
        $this->doSave($username);
    }

    public function delete(Username $username): void
    {
        $this->doDelete($username);
    }
}
