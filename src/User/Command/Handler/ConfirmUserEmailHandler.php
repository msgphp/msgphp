<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Event\Confirm;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\ConfirmUserEmail;
use MsgPhp\User\Event\UserEmailConfirmed;
use MsgPhp\User\Repository\UserEmailRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserEmailHandler
{
    use EventSourcingCommandHandlerTrait;

    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserEmailRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ConfirmUserEmail $command): void
    {
        $userEmail = $this->repository->find($command->email);

        if ($this->handleEvent($userEmail, $this->factory->create(Confirm::class))) {
            $this->repository->save($userEmail);
            $this->bus->dispatch($this->factory->create(UserEmailConfirmed::class, compact('userEmail')));
        }
    }
}
