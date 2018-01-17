<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\{CommandBusInterface, EventBusInterface};
use MsgPhp\User\Command\{ConfirmUserSecondaryEmailCommand, MarkUserSecondaryEmailPrimaryCommand};
use MsgPhp\User\Event\UserSecondaryEmailConfirmedEvent;
use MsgPhp\User\Repository\UserSecondaryEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConfirmUserSecondaryEmailHandler
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

    public function handle(ConfirmUserSecondaryEmailCommand $command): void
    {
        $userSecondaryEmail = $this->repository->findByConfirmationToken($command->token);

        if (null === $userSecondaryEmail->getConfirmedAt()) {
            $userSecondaryEmail->confirm();

            $this->repository->save($userSecondaryEmail);

            if (null !== $this->eventBus) {
                $this->eventBus->handle(new UserSecondaryEmailConfirmedEvent($userSecondaryEmail));
            }
        }

        if ($userSecondaryEmail->isPendingPrimary()) {
            $this->commandBus->handle(new MarkUserSecondaryEmailPrimaryCommand($userSecondaryEmail->getUserId(), $userSecondaryEmail->getEmail()));
        }
    }
}
