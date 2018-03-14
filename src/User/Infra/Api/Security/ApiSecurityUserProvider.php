<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Api\Security;

use Doctrine\ORM\EntityNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\UserProviderWithPayloadSupportsInterface;
use MsgPhp\User\Infra\Security\{UserRolesProviderInterface, SecurityUser};
use MsgPhp\User\UserId;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\{UnsupportedUserException, UsernameNotFoundException};
use Symfony\Component\Security\Core\User\UserInterface;

final class ApiSecurityUserProvider implements UserProviderWithPayloadSupportsInterface
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

    /**
     * The UserId is stored in the payload, so we need to find the user by id and not by username.
     *
     * @param string $username
     * @param array  $payload
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface|void
     */
    public function loadUserByUsernameAndPayload($username, array $payload)
    {
        try {
            $user = $this->repository->find(UserId::fromValue($username));
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user);
    }
}
