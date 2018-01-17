<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\DeleteUserRoleCommand;
use MsgPhp\User\Event\UserRoleDeletedEvent;
use MsgPhp\User\Repository\UserRoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserRoleRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(DeleteUserRoleCommand $command): void
    {
        $userRole = $this->repository->find($command->userId, $command->role);

        $this->repository->delete($userRole);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserRoleDeletedEvent($userRole));
        }
    }
}
