<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\CreateRole;
use MsgPhp\User\Event\RoleCreated;
use MsgPhp\User\Repository\RoleRepository;
use MsgPhp\User\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateRoleHandler
{
    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, RoleRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateRole $command): void
    {
        $context = $command->context;
        $role = $this->factory->create(Role::class, $context);

        $this->repository->save($role);
        $this->bus->dispatch($this->factory->create(RoleCreated::class, compact('role', 'context')));
    }
}
