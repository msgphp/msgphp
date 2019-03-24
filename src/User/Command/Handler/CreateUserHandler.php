<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CreateUser;
use MsgPhp\User\Event\UserCreated;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserHandler
{
    use MessageDispatchingTrait;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateUser $command): void
    {
        $context = $command->context;
        $context['id'] = $context['id'] ?? $this->factory->create(UserId::class);
        $user = $this->factory->create(User::class, $context);

        $this->repository->save($user);
        $this->dispatch(UserCreated::class, compact('user', 'context'));
    }
}
