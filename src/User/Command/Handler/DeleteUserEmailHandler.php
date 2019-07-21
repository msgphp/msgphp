<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\DeleteUserEmail;
use MsgPhp\User\Event\UserEmailDeleted;
use MsgPhp\User\Repository\UserEmailRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserEmailHandler
{
    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserEmailRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteUserEmail $command): void
    {
        try {
            $userEmail = $this->repository->find($command->email);
        } catch (EntityNotFound $e) {
            return;
        }

        $this->repository->delete($userEmail);
        $this->bus->dispatch($this->factory->create(UserEmailDeleted::class, compact('userEmail')));
    }
}
