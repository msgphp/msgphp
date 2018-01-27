<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\DomainMessageBusInterface;
use MsgPhp\Domain\Event\{DomainEventInterface, DisableDomainEvent};
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

    private $bus;
    private $repository;
    private $factory;

    public function __construct(DomainMessageBusInterface $bus, UserRepositoryInterface $repository, DomainObjectFactoryInterface $factory)
    {
        $this->bus = $bus;
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function __invoke(DisableUserCommand $command): void
    {
        $this->doHandle($command, function (User $user): void {
            $this->repository->save($user);
            $this->bus->dispatch($this->factory->create(UserDisabledEvent::class, ['user' => $user]));
        });
    }

    protected function getDomainEvent(DisableUserCommand $command): DomainEventInterface
    {
        return $this->factory->create(DisableDomainEvent::class);
    }

    protected function getDomainEventHandler(DisableUserCommand $command): User
    {
        return $this->repository->find($command->userId);
    }
}
