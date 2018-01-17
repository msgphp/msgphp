<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\DeleteUserSecondaryEmailCommand;
use MsgPhp\User\Event\UserSecondaryEmailDeletedEvent;
use MsgPhp\User\Repository\UserSecondaryEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserSecondaryEmailHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserSecondaryEmailRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(DeleteUserSecondaryEmailCommand $command): void
    {
        $userSecondaryEmail = $this->repository->find($command->userId, $command->email);

        $this->repository->delete($userSecondaryEmail);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserSecondaryEmailDeletedEvent($userSecondaryEmail));
        }
    }
}
