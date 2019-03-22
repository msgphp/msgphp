<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\Role\RoleProviderInterface;
use MsgPhp\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SecurityUserProvider implements UserProviderInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    /**
     * @var RoleProviderInterface|null
     */
    private $roleProvider;

    public function __construct(UserRepositoryInterface $repository, RoleProviderInterface $roleProvider = null)
    {
        $this->repository = $repository;
        $this->roleProvider = $roleProvider;
    }

    public function loadUserByUsername($username): UserInterface
    {
        try {
            $user = $this->repository->findByUsername($username);
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user, $username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Unsupported user "%s".', \get_class($user)));
        }

        $securityUser = $user;

        try {
            $user = $this->repository->find($securityUser->getUserId());
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user, $securityUser->getOriginUsername());
    }

    public function supportsClass($class): bool
    {
        return SecurityUser::class === $class;
    }

    public function fromUser(User $user, string $originUsername = null): SecurityUser
    {
        return new SecurityUser($user, $originUsername, $this->roleProvider ? $this->roleProvider->getRoles($user) : []);
    }
}
