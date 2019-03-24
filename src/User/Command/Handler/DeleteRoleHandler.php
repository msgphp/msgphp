<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\DeleteRole;
use MsgPhp\User\Event\RoleDeleted;
use MsgPhp\User\Repository\RoleRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteRoleHandler
{
    use MessageDispatchingTrait;

    /**
     * @var RoleRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, RoleRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DeleteRole $command): void
    {
        try {
            $role = $this->repository->find($command->roleName);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($role);
        $this->dispatch(RoleDeleted::class, compact('role'));
    }
}
