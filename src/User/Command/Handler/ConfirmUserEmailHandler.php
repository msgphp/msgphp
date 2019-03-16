<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\ConfirmEvent;
use MsgPhp\Domain\Event\DomainEventInterface;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
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

    /**
     * @var UserEmailRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserEmailRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ConfirmUserEmailCommand $command): void
    {
        $this->handle($command, function (UserEmail $userEmail): void {
            $this->repository->save($userEmail);
            $this->dispatch(UserEmailConfirmedEvent::class, compact('userEmail'));
        });
    }

    protected function getDomainEvent(ConfirmUserEmailCommand $command): DomainEventInterface
    {
        return $this->factory->create(ConfirmEvent::class);
    }

    protected function getDomainEventTarget(ConfirmUserEmailCommand $command): UserEmail
    {
        return $this->repository->find($command->email);
    }
}
