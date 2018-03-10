<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Command\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Event\ConfirmEvent;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\ConfirmUserEmailCommand;
use MsgPhp\User\Entity\UserEmail;
use MsgPhp\User\Event\UserEmailConfirmedEvent;
use MsgPhp\User\Repository\UserEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserEmailHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserEmailRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ConfirmUserEmailCommand $command): void
    {
        $this->handle($command, function (UserEmail $userEmail): void {
            $this->repository->save($userEmail);
            $this->dispatch(UserEmailConfirmedEvent::class, [$userEmail]);
        });
    }

    protected function getDomainEvent(ConfirmUserEmailCommand $command): ConfirmEvent
    {
        return $this->factory->create(ConfirmEvent::class);
    }

    protected function getDomainEventHandler(ConfirmUserEmailCommand $command): UserEmail
    {
        return $this->repository->find($command->email);
    }
}
