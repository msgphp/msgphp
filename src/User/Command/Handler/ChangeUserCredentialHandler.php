<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\ChangeUserCredentialCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;
use MsgPhp\User\Event\UserCredentialChangedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserCredentialHandler
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

    public function __invoke(ChangeUserCredentialCommand $command): void
    {
        $oldCredential = $this->getDomainEventHandler($command)->getCredential();

        $this->handle($command, function (User $user) use ($oldCredential): void {
            $this->repository->save($user);
            $this->dispatch(UserCredentialChangedEvent::class, [$user, $oldCredential, $user->getCredential()]);
        });
    }

    protected function getDomainEvent(ChangeUserCredentialCommand $command): ChangeCredentialEvent
    {
        return $this->factory->create(ChangeCredentialEvent::class, [$command->context]);
    }

    protected function getDomainEventHandler(ChangeUserCredentialCommand $command): User
    {
        return $this->repository->find($this->factory->identify(User::class, $command->userId));
    }
}
