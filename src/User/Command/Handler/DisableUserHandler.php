<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Event\{DisableEvent, DomainEventHandlerInterface, DomainEventInterface};
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\DisableUserCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\UserDisabledEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DisableUserHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DisableUserCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserDisabledEvent::class, [$user]);
        });
    }

    protected function getDomainEvent(DisableUserCommand $command): DomainEventInterface
    {
        return $this->factory->create(DisableEvent::class);
    }

    protected function getDomainEventHandler(DisableUserCommand $command): DomainEventHandlerInterface
    {
        return $this->repository->find($command->userId);
    }
}
