<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\EnableUserCommand;
use MsgPhp\User\Event\UserEnabledEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EnableUserHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(EnableUserCommand $command): void
    {
        $user = $this->repository->find($command->userId);

        if ($user->isEnabled()) {
            return;
        }

        $user->enable();

        $this->repository->save($user);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserEnabledEvent($user));
        }
    }
}
