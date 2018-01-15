<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SecurityUserProvider implements UserProviderInterface
{
    private $repository;
    private $factory;
    private $roleProvider;

    public function __construct(UserRepositoryInterface $repository, EntityFactoryInterface $factory, UserRolesProviderInterface $roleProvider = null)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->roleProvider = $roleProvider;
    }

    public function loadUserByUsername($username): UserInterface
    {
        try {
            return $this->createUser($username);
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Unsupported user "%s"', get_class($user)));
        }

        try {
            return $this->createUser($user->getUsername());
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }
    }

    public function supportsClass($class): bool
    {
        return SecurityUser::class === $class;
    }

    private function createUser(string $id): SecurityUser
    {
        $user = $this->repository->find($this->factory->identify(User::class, $id));

        return new SecurityUser($user, $this->roleProvider ? $this->roleProvider->getRoles($user) : []);
    }
}
