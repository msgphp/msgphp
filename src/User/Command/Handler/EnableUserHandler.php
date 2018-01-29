<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Event\EnableDomainEvent;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, DomainMessageDispatchingTrait};
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
    use DomainMessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(EnableUserCommand $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserEnabledEvent::class, [$user]);
        });
    }

    protected function getDomainEvent(EnableUserCommand $command): EnableDomainEvent
    {
        return $this->factory->create(EnableDomainEvent::class);
    }

    protected function getDomainEventHandler(EnableUserCommand $command): User
    {
        return $this->repository->find($this->factory->identify(User::class, $command->userId));
    }
}
