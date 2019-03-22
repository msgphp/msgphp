<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CreateUserCommand;
use MsgPhp\User\Event\UserCreatedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\User;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserHandler
{
    use MessageDispatchingTrait;

    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateUserCommand $command): void
    {
        $context = $command->context;
        $context['id'] = $context['id'] ?? $this->factory->create(UserIdInterface::class);
        $user = $this->factory->create(User::class, $context);

        $this->repository->save($user);
        $this->dispatch(UserCreatedEvent::class, compact('user', 'context'));
    }
}
