<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\User\Command\CancelUserPendingPrimaryEmailCommand;
use MsgPhp\User\Event\UserPendingPrimaryEmailCancelledEvent;
use MsgPhp\User\Repository\UserSecondaryEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CancelUserPendingPrimaryEmailHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserSecondaryEmailRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(CancelUserPendingPrimaryEmailCommand $command): void
    {
        try {
            $userSecondaryEmail = $this->repository->findPendingPrimary($command->userId);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $userSecondaryEmail->markPendingPrimary(false);

        $this->repository->save($userSecondaryEmail);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserPendingPrimaryEmailCancelledEvent($userSecondaryEmail));
        }
    }
}
