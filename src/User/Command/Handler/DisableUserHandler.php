<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\Disable;
use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\DisableUser;
use MsgPhp\User\Event\UserDisabled;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DisableUserHandler
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

    public function __invoke(DisableUser $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserDisabled::class, compact('user'));
        });
    }

    protected function getDomainEvent(DisableUser $command): DomainEvent
    {
        return $this->factory->create(Disable::class);
    }

    protected function getDomainEventTarget(DisableUser $command): User
    {
        return $this->repository->find($command->userId);
    }
}
