<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\DeleteUserCommand;
use MsgPhp\User\Event\UserDeletedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(DeleteUserCommand $command): void
    {
        $user = $this->repository->find($command->userId);

        $this->repository->delete($user);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserDeletedEvent($user));
        }
    }
}
