<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\CommandBusInterface;
use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Command\{AddUserSecondaryEmailCommand, CancelUserPendingPrimaryEmailCommand, SetUserPendingPrimaryEmailCommand};
use MsgPhp\User\Event\UserPendingPrimaryEmailSetEvent;
use MsgPhp\User\Repository\UserSecondaryEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class SetUserPendingPrimaryEmailHandler
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

    public function handle(SetUserPendingPrimaryEmailCommand $command): void
    {
        try {
            if ($command->email === $this->repository->findPendingPrimary($command->userId)->getEmail()) {
                return;
            }

            $this->commandBus->handle(new CancelUserPendingPrimaryEmailCommand($command->userId));
        } catch (EntityNotFoundException $e) {
        }

        try {
            $userSecondaryEmail = $this->repository->find($command->userId, $command->email);
        } catch (EntityNotFoundException $e) {
            $this->commandBus->handle(new AddUserSecondaryEmailCommand($command->userId, $command->email));

            $userSecondaryEmail = $this->repository->find($command->userId, $command->email); // @FIXME
        }

        if ($userSecondaryEmail->isPendingPrimary()) {
            return;
        }

        $userSecondaryEmail->markPendingPrimary();

        $this->repository->save($userSecondaryEmail);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserPendingPrimaryEmailSetEvent($userSecondaryEmail));
        }
    }
}
