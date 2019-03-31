<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait TokenStorageAwareTrait
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(TokenStorageInterface $tokenStorage, UserRepository $repository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
    }

    private function isUser(): bool
    {
        $token = $this->tokenStorage->getToken();

        return null !== $token && $token->getUser() instanceof UserIdentity;
    }

    private function toUser(): ?User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        $identity = $token->getUser();

        if (!$identity instanceof UserIdentity) {
            return null;
        }

        return $this->repository->find($identity->getUserId());
    }
}
