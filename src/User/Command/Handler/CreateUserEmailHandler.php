<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\User\Command\CreateUserEmailCommand;
use MsgPhp\User\Entity\{User, UserEmail};
use MsgPhp\User\Event\UserEmailCreatedEvent;
use MsgPhp\User\Repository\UserEmailRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CreateUserEmailHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserEmailRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(CreateUserEmailCommand $command): void
    {
        $userId = $this->factory->identify(User::class, $command->userId);
        $userEmail = $this->factory->create(UserEmail::class, [
            'user' => $this->factory->reference(User::class, $userId),
            'email' => $command->email,
        ] + $command->context);

        $this->repository->save($userEmail);
        $this->dispatch(UserEmailCreatedEvent::class, [$userEmail]);
    }
}
