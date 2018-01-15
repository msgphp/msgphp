<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserValueResolver implements ArgumentValueResolverInterface
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

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (User::class !== $argument->getType()) {
            return false;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return $argument->isNullable();
        }

        return $token->getUser() instanceof SecurityUser;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            yield null;

            return;
        }

        /** @var SecurityUser $user */
        $user = $token->getUser();

        yield $this->repository->find($this->factory->identify(User::class, $user->getUsername()));
    }
}
