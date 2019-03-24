<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ChangeUserCredential;
use MsgPhp\User\Event\Domain\ChangeCredential;
use MsgPhp\User\Event\UserCredentialChanged;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserCredentialHandler
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

    public function __invoke(ChangeUserCredential $command): void
    {
        /** @var User $handler */
        $handler = $this->getDomainEventTarget($command);
        $oldCredential = $handler->getCredential();

        $this->handle($command, function (User $user) use ($oldCredential): void {
            $newCredential = $user->getCredential();

            $this->repository->save($user);
            $this->dispatch(UserCredentialChanged::class, compact('user', 'oldCredential', 'newCredential'));
        });
    }

    protected function getDomainEvent(ChangeUserCredential $command): DomainEvent
    {
        $fields = $command->fields;

        return $this->factory->create(ChangeCredential::class, compact('fields'));
    }

    protected function getDomainEventTarget(ChangeUserCredential $command): User
    {
        return $this->repository->find($command->userId);
    }
}
