<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\AddUserRole;
use MsgPhp\User\Event\UserRoleAdded;
use MsgPhp\User\Repository\UserRoleRepository;
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
     * @var UserRoleRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRoleRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserRole $command): void
    {
        $context = $command->context;
        $context['user'] = $this->factory->reference(User::class, ['id' => $command->userId]);
        $context['role'] = $this->factory->reference(Role::class, ['name' => $command->roleName]);
        $userRole = $this->factory->create(UserRole::class, $context);

        $this->repository->save($userRole);
        $this->dispatch(UserRoleAdded::class, compact('userRole', 'context'));
    }
}
