<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\AddUserRoleCommand;
use MsgPhp\User\Event\UserRoleAddedEvent;
use MsgPhp\User\Repository\UserRoleRepositoryInterface;
use MsgPhp\User\Role;
use MsgPhp\User\User;
use MsgPhp\User\UserRole;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleHandler
{
    use MessageDispatchingTrait;

    /**
     * @var UserRoleRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserRoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserRoleCommand $command): void
    {
        $context = $command->context;
        $context['user'] = $this->factory->reference(User::class, ['id' => $command->userId]);
        $context['role'] = $this->factory->reference(Role::class, ['name' => $command->roleName]);
        $userRole = $this->factory->create(UserRole::class, $context);

        $this->repository->save($userRole);
        $this->dispatch(UserRoleAddedEvent::class, compact('userRole', 'context'));
    }
}
