<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\AddUserEmailCommand;
use MsgPhp\User\Entity\{User, UserEmail};
use MsgPhp\User\Event\UserEmailAddedEvent;
use MsgPhp\User\Repository\UserEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserEmailHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserEmailRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserEmailCommand $command): void
    {
        $userEmail = $this->factory->create(UserEmail::class, [
            'user' => $this->factory->reference(User::class, $command->userId),
            'email' => $command->email,
        ] + $command->context);

        $this->repository->save($userEmail);
        $this->dispatch(UserEmailAddedEvent::class, [$userEmail]);
    }
}
