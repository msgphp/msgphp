<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\User\Command\AddUserSecondaryEmailCommand;
use MsgPhp\User\Entity\UserSecondaryEmail;
use MsgPhp\User\Event\UserSecondaryEmailAddedEvent;
use MsgPhp\User\Repository\{UserRepositoryInterface, UserSecondaryEmailRepositoryInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserSecondaryEmailHandler
{
    private $userRepository;
    private $userSecondaryEmailRepository;
    private $factory;
    private $eventBus;

    public function __construct(UserRepositoryInterface $userRepository, UserSecondaryEmailRepositoryInterface $userSecondaryEmailRepository, EntityFactoryInterface $factory, EventBusInterface $eventBus = null)
    {
        $this->userRepository = $userRepository;
        $this->userSecondaryEmailRepository = $userSecondaryEmailRepository;
        $this->factory = $factory;
        $this->eventBus = $eventBus;
    }

    public function handle(AddUserSecondaryEmailCommand $command): void
    {
        $userSecondaryEmail = $this->factory->create(UserSecondaryEmail::class, [
            'user' => $this->userRepository->find($command->userId),
            'email' => $command->email,
        ] + $command->context);

        if ($command->confirm) {
            $userSecondaryEmail->confirm();
        }

        $this->userSecondaryEmailRepository->save($userSecondaryEmail);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserSecondaryEmailAddedEvent($userSecondaryEmail));
        }
    }
}
