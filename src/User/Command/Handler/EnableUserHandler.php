<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\DomainMessageBusInterface;
use MsgPhp\Domain\Event\{DomainEventInterface, EnableDomainEvent};
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\User\Command\EnableUserCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\UserEnabledEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EnableUserHandler
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

    public function __invoke(EnableUserCommand $command): void
    {
        $this->doHandle($command, function (User $user): void {
            $this->repository->save($user);
            $this->bus->dispatch($this->factory->create(UserEnabledEvent::class, ['user' => $user]));
        });
    }

    protected function getDomainEvent(EnableUserCommand $command): DomainEventInterface
    {
        return $this->factory->create(EnableDomainEvent::class);
    }

    protected function getDomainEventHandler(EnableUserCommand $command): User
    {
        return $this->repository->find($command->userId);
    }
}
