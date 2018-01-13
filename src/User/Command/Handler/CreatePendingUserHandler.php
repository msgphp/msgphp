<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\User\Command\CreatePendingUserCommand;
use MsgPhp\User\Entity\PendingUser;
use MsgPhp\User\Event\PendingUserCreatedEvent;
use MsgPhp\User\Password\PasswordHashingInterface;
use MsgPhp\User\Repository\PendingUserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreatePendingUserHandler
{
    private $repository;
    private $factory;
    private $passwordHashing;
    private $eventBus;

    public function __construct(PendingUserRepositoryInterface $repository, EntityFactoryInterface $factory, PasswordHashingInterface $passwordHashing, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->passwordHashing = $passwordHashing;
        $this->eventBus = $eventBus;
    }

    public function handle(CreatePendingUserCommand $command): void
    {
        $pendingUser = $this->factory->create(PendingUser::class, [
            'email' => $command->email,
            'password' => $command->plainPassword ? $this->passwordHashing->hash($command->password) : $command->password,
        ] + $command->context);

        $this->repository->save($pendingUser);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new PendingUserCreatedEvent($pendingUser));
        }
    }
}
