<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\Enable;
use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\EnableUser;
use MsgPhp\User\Event\UserEnabled;
use MsgPhp\User\Repository\UserRepository;
use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EnableUserHandler
{
    use EventSourcingCommandHandlerTrait;
    use MessageDispatchingTrait;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(EnableUser $command): void
    {
        $this->handle($command, function (User $user): void {
            $this->repository->save($user);
            $this->dispatch(UserEnabled::class, compact('user'));
        });
    }

    protected function getDomainEvent(EnableUser $command): DomainEvent
    {
        return $this->factory->create(Enable::class);
    }

    protected function getDomainEventTarget(EnableUser $command): User
    {
        return $this->repository->find($command->userId);
    }
}
