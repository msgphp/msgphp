<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CreateRoleCommand;
use MsgPhp\User\Event\RoleCreatedEvent;
use MsgPhp\User\Repository\RoleRepositoryInterface;
use MsgPhp\User\Role;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateRoleHandler
{
    use MessageDispatchingTrait;

    /**
     * @var RoleRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, RoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateRoleCommand $command): void
    {
        $context = $command->context;
        $role = $this->factory->create(Role::class, $context);
        $this->repository->save($role);
        $this->dispatch(RoleCreatedEvent::class, compact('role', 'context'));
    }
}
