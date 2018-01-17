<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\DisableUserCommand;
use MsgPhp\User\Event\UserDisabledEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DisableUserHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(DisableUserCommand $command): void
    {
        $user = $this->repository->find($command->userId);

        if (!$user->isEnabled()) {
            return;
        }

        $user->disable();

        $this->repository->save($user);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserDisabledEvent($user));
        }
    }
}
