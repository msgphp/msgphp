<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security\Jwt;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\UserProviderWithPayloadSupportsInterface;
use MsgPhp\User\Infra\Security\SecurityUserProvider as BaseSecurityUserProvider;

final class SecurityUserProvider extends BaseSecurityUserProvider implements UserProviderWithPayloadSupportsInterface
{
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
        return $this->loadUserById($username);
    }
}
