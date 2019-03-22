<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\User\Command\AddUserEmailCommand;
use MsgPhp\User\Event\UserEmailAddedEvent;
use MsgPhp\User\Repository\UserEmailRepositoryInterface;
use MsgPhp\User\User;
use MsgPhp\User\UserEmail;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserEmailHandler
{
    use MessageDispatchingTrait;

    /**
     * @var UserEmailRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserEmailRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserEmailCommand $command): void
    {
        $context = $command->context;
        $context['user'] = $this->factory->reference(User::class, ['id' => $command->userId]);
        $context['email'] = $command->email;
        $userEmail = $this->factory->create(UserEmail::class, $context);

        $this->repository->save($userEmail);
        $this->dispatch(UserEmailAddedEvent::class, compact('userEmail', 'context'));
    }
}
