<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\RequestUserPasswordCommand;
use MsgPhp\User\Event\UserPasswordRequestedEvent;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class RequestUserPasswordHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(RequestUserPasswordCommand $command): void
    {
        $user = $this->repository->findByEmail($command->email);

        $user->requestPassword();

        $this->repository->save($user);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserPasswordRequestedEvent($user));
        }
    }
}
