<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Event\{DomainEventHandlerInterface, DomainEventInterface};
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\RequestUserPasswordCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\Domain\RequestPasswordEvent;
use MsgPhp\User\Event\UserPasswordRequestedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class RequestUserPasswordHandler
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

    public function __invoke(RequestUserPasswordCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserPasswordRequestedEvent::class, [$user]);
        });
    }

    protected function getDomainEvent(RequestUserPasswordCommand $command): DomainEventInterface
    {
        return $this->factory->create(RequestPasswordEvent::class, [$command->token]);
    }

    protected function getDomainEventHandler(RequestUserPasswordCommand $command): DomainEventHandlerInterface
    {
        return $this->repository->find($command->userId);
    }
}
