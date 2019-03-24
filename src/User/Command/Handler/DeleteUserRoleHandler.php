<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Exception\EntityNotFoundException;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\DeleteUserRole;
use MsgPhp\User\Event\UserRoleDeleted;
use MsgPhp\User\Repository\UserRoleRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserRoleHandler
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

    public function __invoke(DeleteUserRole $command): void
    {
        try {
            $userRole = $this->repository->find($command->userId, $command->roleName);
        } catch (EntityNotFoundException $e) {
            return;
        }

        $this->repository->delete($userRole);
        $this->dispatch(UserRoleDeleted::class, compact('userRole'));
    }
}
