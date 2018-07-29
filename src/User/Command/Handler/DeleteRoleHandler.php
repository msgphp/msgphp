<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\DeleteRoleCommand;
use MsgPhp\User\Event\RoleDeletedEvent;
use MsgPhp\User\Repository\RoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteRoleHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, RoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteRoleCommand $command): void
    {
        try {
            $role = $this->repository->find($command->roleName);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($role);
        $this->dispatch(RoleDeletedEvent::class, [$role]);
    }
}
