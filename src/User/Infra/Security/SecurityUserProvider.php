<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
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
    private $roleProvider;

    public function __construct(UserRepositoryInterface $repository, UserRoleProviderInterface $roleProvider = null)
    {
        $this->repository = $repository;
        $this->roleProvider = $roleProvider;
    }

    public function loadUserByUsername(/*string */$username): UserInterface
    {
        try {
            return $this->create($this->repository->findByEmail($username));
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
            return $this->create($this->repository->find($user->getId()));
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }
    }

    public function supportsClass(/*string */$class): bool
    {
        return SecurityUser::class === $class;
    }

    public function create(User $user): SecurityUser
    {
        return new SecurityUser($user, $this->roleProvider ? $this->roleProvider->getRoles($user) : []);
    }
}
