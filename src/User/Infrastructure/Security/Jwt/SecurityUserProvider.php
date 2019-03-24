<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security\Jwt;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Infrastructure\Security\SecurityUserProvider as BaseSecurityUserProvider;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\UserId;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Damien Merchier <damien.merchier@gmail.com>
 */
final class SecurityUserProvider implements PayloadAwareUserProviderInterface
{
    /**
     * @var BaseSecurityUserProvider
     */
    private $provider;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var DomainObjectFactory
     */
    private $factory;

    public function __construct(BaseSecurityUserProvider $provider, UserRepository $repository, DomainObjectFactory $factory)
    {
        $this->provider = $provider;
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function loadUserByUsernameAndPayload($username, array $payload): UserInterface
    {
        try {
            $user = $this->repository->find($this->factory->create(UserId::class, ['value' => $username]));
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $this->provider->fromUser($user);
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
}
