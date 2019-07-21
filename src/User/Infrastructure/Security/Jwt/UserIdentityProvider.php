<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security\Jwt;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Infrastructure\Security\UserIdentity;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider as BaseUserIdentityProvider;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\UserId;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Damien Merchier <damien.merchier@gmail.com>
 */
final class UserIdentityProvider implements PayloadAwareUserProviderInterface
{
    private $provider;
    private $repository;
    private $factory;

    public function __construct(BaseUserIdentityProvider $provider, UserRepository $repository, DomainObjectFactory $factory)
    {
        $this->provider = $provider;
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * @inheritDoc
     *
     * @return UserIdentity
     */
    public function loadUserByUsernameAndPayload($username, array $payload): UserInterface
    {
        try {
            $user = $this->repository->find($this->factory->create(UserId::class, ['value' => $username]));
        } catch (EntityNotFound $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->provider->fromUser($user);
    }

    /**
     * @inheritDoc
     *
     * @return UserIdentity
     */
    public function loadUserByUsername($username): UserInterface
    {
        return $this->provider->loadUserByUsername($username);
    }

    /**
     * @inheritDoc
     *
     * @return UserIdentity
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->provider->refreshUser($user);
    }

    public function supportsClass($class): bool
    {
        return $this->provider->supportsClass($class);
    }
}
