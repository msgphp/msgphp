<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait TokenStorageAwareTrait
{
    private $tokenStorage;
    private $repository;
    private $factory;

    public function __construct(TokenStorageInterface $tokenStorage, UserRepositoryInterface $repository, EntityFactoryInterface $factory)
    {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
        $this->factory = $factory;
    }

    private function isUser(): bool
    {
        return null !== ($token = $this->tokenStorage->getToken()) && $token->getUser() instanceof SecurityUser;
    }

    private function toUser(): ?User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof SecurityUser) {
            return null;
        }

        return $this->repository->find($this->factory->identify(User::class, $user->getUsername()));
    }
}
