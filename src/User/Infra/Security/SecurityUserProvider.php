<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
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

    public function __construct(UserRepositoryInterface $repository, EntityAwareFactoryInterface $factory, UserRolesProviderInterface $roleProvider = null)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->roleProvider = $roleProvider;
    }

    public function loadUserByUsername($username): UserInterface
    {
        try {
            $user = $this->repository->findByUsername($username);
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Unsupported user "%s"', get_class($user)));
        }

        try {
            $user = $this->repository->find($this->factory->identify(User::class, $user->getUsername()));
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user);
    }

    public function supportsClass($class): bool
    {
        return SecurityUser::class === $class;
    }

    private function fromUser(User $user): SecurityUser
    {
        return new SecurityUser($user, $this->roleProvider ? $this->roleProvider->getRoles($user) : []);
    }
}
