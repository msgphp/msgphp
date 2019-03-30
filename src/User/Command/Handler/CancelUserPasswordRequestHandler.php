<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CancelUserPasswordRequest;
use MsgPhp\User\Event\Domain\CancelPasswordRequest;
use MsgPhp\User\Event\UserPasswordRequestCanceled;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CancelUserPasswordRequestHandler
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

    public function __invoke(CancelUserPasswordRequest $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserPasswordRequestCanceled::class, compact('user'));
        });
    }

    protected function getDomainEvent(CancelUserPasswordRequest $command): DomainEvent
    {
        return $this->factory->create(CancelPasswordRequest::class);
    }

    protected function getDomainEventTarget(CancelUserPasswordRequest $command): User
    {
        return $this->repository->find($command->userId);
    }
}
