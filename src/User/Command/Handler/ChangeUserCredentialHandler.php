<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DomainEventInterface;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
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

    public function __invoke(ChangeUserCredentialCommand $command): void
    {
        /** @var User $handler */
        $handler = $this->getDomainEventTarget($command);
        $oldCredential = $handler->getCredential();

        $this->handle($command, function (User $user) use ($oldCredential): void {
            $newCredential = $user->getCredential();

            $this->repository->save($user);
            $this->dispatch(UserCredentialChangedEvent::class, compact('user', 'oldCredential', 'newCredential'));
        });
    }

    protected function getDomainEvent(ChangeUserCredentialCommand $command): DomainEventInterface
    {
        $fields = $command->fields;

        return $this->factory->create(ChangeCredentialEvent::class, compact('fields'));
    }

    protected function getDomainEventTarget(ChangeUserCredentialCommand $command): User
    {
        return $this->repository->find($command->userId);
    }
}
