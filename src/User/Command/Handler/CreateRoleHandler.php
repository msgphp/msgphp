<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CreateRole;
use MsgPhp\User\Event\RoleCreated;
use MsgPhp\User\Repository\RoleRepository;
use MsgPhp\User\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateRoleHandler
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

    public function __invoke(CreateRole $command): void
    {
        $context = $command->context;
        $role = $this->factory->create(Role::class, $context);
        $this->repository->save($role);
        $this->dispatch(RoleCreated::class, compact('role', 'context'));
    }
}
