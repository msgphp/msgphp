<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\Confirm;
use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\ConfirmUserEmail;
use MsgPhp\User\Event\UserEmailConfirmed;
use MsgPhp\User\Repository\UserEmailRepository;
use MsgPhp\User\UserEmail;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserEmailHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    /**
     * @var UserEmailRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserEmailRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ConfirmUserEmail $command): void
    {
        $this->handle($command, function (UserEmail $userEmail): void {
            $this->repository->save($userEmail);
            $this->dispatch(UserEmailConfirmed::class, compact('userEmail'));
        });
    }

    protected function getDomainEvent(ConfirmUserEmail $command): DomainEvent
    {
        return $this->factory->create(Confirm::class);
    }

    protected function getDomainEventTarget(ConfirmUserEmail $command): UserEmail
    {
        return $this->repository->find($command->email);
    }
}
