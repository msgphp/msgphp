<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DisableEvent;
use MsgPhp\Domain\Event\DomainEventInterface;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\DisableUserCommand;
use MsgPhp\User\Event\UserDisabledEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DisableUserHandler
{
    use EventSourcingCommandHandlerTrait;
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

    public function __invoke(DisableUserCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserDisabledEvent::class, compact('user'));
        });
    }

    protected function getDomainEvent(DisableUserCommand $command): DomainEventInterface
    {
        return $this->factory->create(DisableEvent::class);
    }

    protected function getDomainEventTarget(DisableUserCommand $command): User
    {
        return $this->repository->find($command->userId);
    }
}
