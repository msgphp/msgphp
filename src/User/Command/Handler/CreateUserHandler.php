<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\User\Command\CreateUserCommand;
use MsgPhp\User\Entity\User;
use MsgPhp\User\Event\UserCreatedEvent;
use MsgPhp\User\Password\PasswordHashingInterface;
use MsgPhp\User\Repository\UserRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserHandler
{
    private $repository;
    private $factory;
    private $passwordHashing;
    private $eventBus;

    public function __construct(UserRepositoryInterface $repository, EntityFactoryInterface $factory, PasswordHashingInterface $passwordHashing, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->passwordHashing = $passwordHashing;
        $this->eventBus = $eventBus;
    }

    public function handle(CreateUserCommand $command): void
    {
        $user = $this->factory->create(User::class, [
            'id' => null === $command->userId ? $this->factory->nextIdentity(User::class) : $command->userId,
            'email' => $command->email,
            'password' => $command->plainPassword ? $this->passwordHashing->hash($command->password) : $command->password,
        ] + $command->context);

        if ($command->enable) {
            $user->enable(); // @fixme Feature depending availability + dispatch EnableUserCommand
        }

        $this->repository->save($user);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserCreatedEvent($user));
        }
    }
}
