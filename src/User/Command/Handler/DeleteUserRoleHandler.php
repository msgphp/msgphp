<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\DeleteUserRoleCommand;
use MsgPhp\User\Event\UserRoleDeletedEvent;
use MsgPhp\User\Repository\UserRoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteUserRoleCommand $command): void
    {
        try {
            $userRole = $this->repository->find($command->userId, $command->roleName);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($userRole);
        $this->dispatch(UserRoleDeletedEvent::class, [$userRole]);
    }
}
