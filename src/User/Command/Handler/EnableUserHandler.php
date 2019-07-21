<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Event\Enable;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\EnableUser;
use MsgPhp\User\Event\UserEnabled;
use MsgPhp\User\Repository\UserRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EnableUserHandler
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

    public function __invoke(EnableUser $command): void
    {
        $user = $this->repository->find($command->userId);

        if ($this->handleEvent($user, $this->factory->create(Enable::class))) {
            $this->repository->save($user);
            $this->bus->dispatch($this->factory->create(UserEnabled::class, compact('user')));
        }
    }
}
