<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\{CommandBusInterface, EventBusInterface};
use MsgPhp\User\Command\{AddUserSecondaryEmailCommand, ChangeUserEmailCommand, DeleteUserSecondaryEmailCommand, MarkUserSecondaryEmailPrimaryCommand, SetUserPendingPrimaryEmailCommand};
use MsgPhp\User\Event\UserSecondaryEmailMarkedPrimaryEvent;
use MsgPhp\User\Repository\UserSecondaryEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class MarkUserSecondaryEmailPrimaryHandler
{
    private $repository;
    private $commandBus;
    private $eventBus;

    public function __construct(UserSecondaryEmailRepositoryInterface $repository, CommandBusInterface $commandBus, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
    }

    public function handle(MarkUserSecondaryEmailPrimaryCommand $command): void
    {
        $userSecondaryEmail = $this->repository->find($command->userId, $command->email);
        $currentPrimaryEmail = $userSecondaryEmail->getUser()->getEmail();

        if (null !== $userSecondaryEmail->getConfirmedAt()) {
            $this->commandBus->handle(new ChangeUserEmailCommand($command->userId, $command->email));
            $this->commandBus->handle(new DeleteUserSecondaryEmailCommand($command->userId, $command->email));
            $this->commandBus->handle(new AddUserSecondaryEmailCommand($command->userId, $currentPrimaryEmail, true));
        } else {
            $this->commandBus->handle(new SetUserPendingPrimaryEmailCommand($command->userId, $command->email));
        }

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserSecondaryEmailMarkedPrimaryEvent($userSecondaryEmail));
        }
    }
}
