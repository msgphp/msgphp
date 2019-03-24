<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Repository\UsernameRepository;
use MsgPhp\User\Repository\UserRepository as BaseUserRepository;
use MsgPhp\User\User;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserRepository implements BaseUserRepository
{
    use DomainEntityRepositoryTrait;

    /**
     * @var string|null
     */
    private $usernameField;

    /**
     * @var UsernameRepository|null
     */
    private $usernameRepository;

    /**
     * @psalm-param class-string $class
     */
    public function __construct(string $class, EntityManagerInterface $em, string $usernameField = null, UsernameRepository $usernameRepository = null)
    {
        $this->class = $class;
        $this->em = $em;
        $this->usernameField = $usernameField;
        $this->usernameRepository = $usernameRepository;
    }

    /**
     * @return DomainCollection|User[]
     */
    public function findAll(int $offset = 0, int $limit = 0): DomainCollection
    {
        return $this->doFindAll($offset, $limit);
    }

    public function find(UserId $id): User
    {
        return $this->doFind($id);
    }

    public function findByUsername(string $username): User
    {
        if (null !== $this->usernameRepository) {
            return $this->usernameRepository->find($username)->getUser();
        }

        if (null === $this->usernameField) {
            throw new \LogicException('User has no username field.');
        }

        return $this->doFindByFields([$this->usernameField => $username]);
    }

    public function exists(UserId $id): bool
    {
        return $this->doExists($id);
    }

    public function usernameExists(string $username): bool
    {
        if (null !== $this->usernameRepository) {
            return $this->usernameRepository->exists($username);
        }

        if (null === $this->usernameField) {
            throw new \LogicException('User has no username field.');
        }

        return $this->doExistsByFields([$this->usernameField => $username]);
    }

    public function save(User $user): void
    {
        $this->doSave($user);
    }

    public function delete(User $user): void
    {
        $this->doDelete($user);
    }
}
