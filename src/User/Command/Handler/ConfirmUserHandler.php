<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\Confirm;
use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ConfirmUser;
use MsgPhp\User\Event\UserConfirmed;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserHandler
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

    public function __invoke(ConfirmUser $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserConfirmed::class, compact('user'));
        });
    }

    protected function getDomainEvent(ConfirmUser $command): DomainEvent
    {
        return $this->factory->create(Confirm::class);
    }

    protected function getDomainEventTarget(ConfirmUser $command): User
    {
        return $this->repository->find($command->userId);
    }
}
