<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\ConfirmEvent;
use MsgPhp\Domain\Event\DomainEventInterface;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ConfirmUserCommand;
use MsgPhp\User\Event\UserConfirmedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserHandler
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

    public function __invoke(ConfirmUserCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserConfirmedEvent::class, compact('user'));
        });
    }

    protected function getDomainEvent(ConfirmUserCommand $command): DomainEventInterface
    {
        return $this->factory->create(ConfirmEvent::class);
    }

    protected function getDomainEventTarget(ConfirmUserCommand $command): User
    {
        return $this->repository->find($command->userId);
    }
}
