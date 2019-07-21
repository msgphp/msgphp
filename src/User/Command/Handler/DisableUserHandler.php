<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Event\Disable;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\DisableUser;
use MsgPhp\User\Event\UserDisabled;
use MsgPhp\User\Repository\UserRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DisableUserHandler
{
    use EventSourcingCommandHandlerTrait;

    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(DisableUser $command): void
    {
        $user = $this->repository->find($command->userId);

        if ($this->handleEvent($user, $this->factory->create(Disable::class))) {
            $this->repository->save($user);
            $this->bus->dispatch($this->factory->create(UserDisabled::class, compact('user')));
        }
    }
}
