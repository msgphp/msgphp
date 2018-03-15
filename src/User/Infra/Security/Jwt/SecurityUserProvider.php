<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security\Jwt;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\UserProviderWithPayloadSupportsInterface;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Infra\Security\SecurityUser;
use MsgPhp\User\Infra\Security\SecurityUserProvider as BaseSecurityUserProvider;
use MsgPhp\User\Infra\Security\UserRolesProviderInterface;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Damien Merchier <damien.merchier@gmail.com>
 */
final class SecurityUserProvider implements UserProviderWithPayloadSupportsInterface
{
    private $provider;
    private $repository;
    private $factory;
    private $roleProvider;

    public function __construct(BaseSecurityUserProvider $provider, UserRepositoryInterface $repository, EntityAwareFactoryInterface $factory, UserRolesProviderInterface $roleProvider = null)
    {
        $this->provider = $provider;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->roleProvider = $roleProvider;
    }

    public function loadUserByUsernameAndPayload($username, array $payload): UserInterface
    {
        try {
            $user = $this->repository->find($this->factory->identify(User::class, $username));
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->fromUser($user);
    }

    public function loadUserByUsername($username): UserInterface
    {
        return $this->provider->loadUserByUsername($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->provider->refreshUser($user);
    }

    public function supportsClass($class): bool
    {
        return $this->provider->supportsClass($class);
    }

    private function fromUser(User $user): SecurityUser
    {
        return new SecurityUser($user, $this->roleProvider ? $this->roleProvider->getRoles($user) : []);
    }
}
