<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security\Jwt;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\UserProviderWithPayloadSupportsInterface;
use MsgPhp\User\Infra\Security\SecurityUserProvider as BaseSecurityUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUserProvider implements UserProviderWithPayloadSupportsInterface
{
    private $provider;

    public function __construct(BaseSecurityUserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * The UserId is stored in the payload, so we need to find the user by id and not by username.
     *
     * @param string $username
     * @param array  $payload
     *
     * @return \MsgPhp\User\Infra\Security\SecurityUser|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function loadUserByUsernameAndPayload($username, array $payload)
    {
        return $this->provider->loadUserById($username);
    }

    public function loadUserByUsername($username)
    {
        return $this->provider->loadUserByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->provider->refreshUser($user);
    }

    public function supportsClass($class)
    {
        return $this->provider->supportsClass($class);
    }
}
