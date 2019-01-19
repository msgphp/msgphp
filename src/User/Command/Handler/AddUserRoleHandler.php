<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\AddUserRoleCommand;
use MsgPhp\User\Entity\{Role, User, UserRole};
use MsgPhp\User\Event\UserRoleAddedEvent;
use MsgPhp\User\Repository\UserRoleRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserRoleHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserRoleRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserRoleCommand $command): void
    {
        $userRole = $this->factory->create(UserRole::class, [
            'user' => $this->factory->reference(User::class, $command->userId),
            'role' => $this->factory->reference(Role::class, $command->roleName),
        ] + $command->context);

        $this->repository->save($userRole);
        $this->dispatch(UserRoleAddedEvent::class, [$userRole]);
    }
}
