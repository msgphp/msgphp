<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\UserIdInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @todo remove
 */
final class SecurityUserFactory
{
    private $tokenStorage;
    private $repository;

    public function __construct(TokenStorageInterface $tokenStorage, UserRepositoryInterface $repository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
    }

    public function isAuthenticated()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return false;
        }

        return $token->isAuthenticated() && $token->getUser() instanceof SecurityUser;
    }

    public function getUserId(): UserIdInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            throw new TokenNotFoundException();
        }

        if (!$token->isAuthenticated()) {
            throw new AuthenticationExpiredException();
        }

        $user = $token->getUser();
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException();
        }

        return $user->getId();
    }

    public function getUser(): User
    {
        try {
            return $this->repository->find($this->getUserId());
        } catch (EntityNotFoundException $e) {
            throw new UsernameNotFoundException();
        }
    }
}
