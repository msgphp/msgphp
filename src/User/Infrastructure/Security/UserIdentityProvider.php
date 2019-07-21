<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\Role\RoleProvider;
use MsgPhp\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserIdentityProvider implements UserProviderInterface
{
    private $repository;
    private $roleProvider;

    public function __construct(UserRepository $repository, RoleProvider $roleProvider = null)
    {
        $this->repository = $repository;
        $this->roleProvider = $roleProvider;
    }

    /**
     * @inheritDoc
     *
     * @return UserIdentity
     */
    public function loadUserByUsername($username): UserInterface
    {
        try {
            $user = $this->repository->findByUsername($username);
        } catch (EntityNotFound $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user, $username);
    }

    /**
     * @inheritDoc
     *
     * @return UserIdentity
     */
    public function refreshUser(UserInterface $identity): UserInterface
    {
        if (!$identity instanceof UserIdentity) {
            throw new UnsupportedUserException('Unsupported user "'.\get_class($identity).'".');
        }

        try {
            $user = $this->repository->find($identity->getUserId());
        } catch (EntityNotFound $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user, $identity->getOriginUsername());
    }

    public function supportsClass($class): bool
    {
        return UserIdentity::class === $class;
    }

    public function fromUser(User $user, string $originUsername = null): UserIdentity
    {
        return new UserIdentity($user, $originUsername, $this->roleProvider ? $this->roleProvider->getRoles($user) : []);
    }
}
