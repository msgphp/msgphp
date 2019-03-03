<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DomainEventHandlerInterface;
use MsgPhp\Domain\Event\DomainEventInterface;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
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

    public function __invoke(RequestUserPasswordCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserPasswordRequestedEvent::class, compact('user'));
        });
    }

    protected function getDomainEvent(RequestUserPasswordCommand $command): DomainEventInterface
    {
        $token = $command->token;

        return $this->factory->create(RequestPasswordEvent::class, compact('token'));
    }

    protected function getDomainEventHandler(RequestUserPasswordCommand $command): DomainEventHandlerInterface
    {
        return $this->repository->find($command->userId);
    }
}
