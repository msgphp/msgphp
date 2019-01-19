<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\CreateUserCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\UserCreatedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateUserCommand $command): void
    {
        $user = $this->factory->create(User::class, $command->context + ['id' => $this->factory->nextIdentifier(User::class)]);

        $this->repository->save($user);
        $this->dispatch(UserCreatedEvent::class, compact('user'));
    }
}
