<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Event\{ConfirmEvent, DomainEventHandlerInterface, DomainEventInterface};
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\ConfirmUserCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\UserConfirmedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserHandler
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

    public function __invoke(ConfirmUserCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserConfirmedEvent::class, [$user]);
        });
    }

    protected function getDomainEvent(ConfirmUserCommand $command): DomainEventInterface
    {
        return $this->factory->create(ConfirmEvent::class);
    }

    protected function getDomainEventHandler(ConfirmUserCommand $command): DomainEventHandlerInterface
    {
        return $this->repository->find($this->factory->identify(User::class, $command->userId));
    }
}
