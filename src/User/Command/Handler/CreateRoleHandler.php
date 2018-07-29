<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\CreateRoleCommand;
use MsgPhp\User\Entity\Role;
use MsgPhp\User\Event\RoleCreatedEvent;
use MsgPhp\User\Repository\RoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateRoleHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, RoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateRoleCommand $command): void
    {
        $role = $this->factory->create(Role::class, $command->context);
        $this->repository->save($role);
        $this->dispatch(RoleCreatedEvent::class, [$role]);
    }
}
