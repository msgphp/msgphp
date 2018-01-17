<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\ChangeUserPasswordCommand;
use MsgPhp\User\Event\UserPasswordChangedEvent;
use MsgPhp\User\Password\PasswordHashingInterface;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserPasswordHandler
{
    private $repository;
    private $passwordHashing;
    private $eventBus;

    public function __construct(UserRepositoryInterface $repository, PasswordHashingInterface $passwordHashing, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->passwordHashing = $passwordHashing;
        $this->eventBus = $eventBus;
    }

    public function handle(ChangeUserPasswordCommand $command): void
    {
        $user = $this->repository->find($command->userId);
        $password = $this->passwordHashing->hash($command->password);

        if ($password === $oldPassword = $user->getPassword()) {
            return;
        }

        $user->changePassword($password);

        $this->repository->save($user);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserPasswordChangedEvent($user, $oldPassword));
        }
    }
}
