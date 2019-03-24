<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\RequestUserPassword;
use MsgPhp\User\Event\Domain\RequestPassword;
use MsgPhp\User\Event\UserPasswordRequested;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class RequestUserPasswordHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(RequestUserPassword $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserPasswordRequested::class, compact('user'));
        });
    }

    protected function getDomainEvent(RequestUserPassword $command): DomainEvent
    {
        $token = $command->token;

        return $this->factory->create(RequestPassword::class, compact('token'));
    }

    protected function getDomainEventTarget(RequestUserPassword $command): User
    {
        return $this->repository->find($command->userId);
    }
}
