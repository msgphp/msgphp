<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, DomainMessageDispatchingTrait};
use MsgPhp\Domain\Event\DisableDomainEvent;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
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
    use DomainMessageDispatchingTrait;

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
            $this->dispatch(UserDisabledEvent::class, [$user]);
        });
    }

    protected function getDomainEvent(DisableUserCommand $command): DisableDomainEvent
    {
        return $this->factory->create(DisableDomainEvent::class);
    }

    protected function getDomainEventHandler(DisableUserCommand $command): User
    {
        return $this->repository->find($command->userId);
    }
}
