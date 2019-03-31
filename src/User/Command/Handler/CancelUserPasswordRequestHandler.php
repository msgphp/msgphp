<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Event\EventSourcingCommandHandlerTrait;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\CancelUserPasswordRequest;
use MsgPhp\User\Event\Domain\CancelPasswordRequest;
use MsgPhp\User\Event\UserPasswordRequestCanceled;
use MsgPhp\User\Repository\UserRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CancelUserPasswordRequestHandler
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

    public function __invoke(CancelUserPasswordRequest $command): void
    {
        $user = $this->repository->find($command->userId);

        if ($this->handleEvent($user, $this->factory->create(CancelPasswordRequest::class))) {
            $this->repository->save($user);
            $this->dispatch(UserPasswordRequestCanceled::class, compact('user'));
        }
    }
}
