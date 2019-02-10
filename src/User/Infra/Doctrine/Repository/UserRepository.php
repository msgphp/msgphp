<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\{UsernameRepositoryInterface, UserRepositoryInterface};
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserRepository implements UserRepositoryInterface
{
    use DomainEntityRepositoryTrait;

    /**
     * @var string|null
     */
    private $usernameField;

    /**
     * @var UsernameRepositoryInterface|null
     */
    private $usernameRepository;

    /**
     * @psalm-param class-string $class
     */
    public function __construct(string $class, EntityManagerInterface $em, string $usernameField = null, UsernameRepositoryInterface $usernameRepository = null)
    {
        $this->class = $class;
        $this->em = $em;
        $this->usernameField = $usernameField;
        $this->usernameRepository = $usernameRepository;
    }

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
        if (null !== $this->usernameRepository) {
            return $this->usernameRepository->find($username)->getUser();
        }

        if (null === $this->usernameField) {
            throw new \LogicException('User has no username field.');
        }

        return $this->doFindByFields([$this->usernameField => $username]);
    }

    public function exists(UserIdInterface $id): bool
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
