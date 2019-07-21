<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\DeleteUser;
use MsgPhp\User\Event\UserDeleted;
use MsgPhp\User\Repository\UserRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserHandler
{
    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteUser $command): void
    {
        try {
            $user = $this->repository->find($command->userId);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($user);
        $this->bus->dispatch($this->factory->create(UserDeleted::class, compact('user')));
    }
}
