<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\User\Command\DeleteUserAttributeValueCommand;
use MsgPhp\User\Event\UserAttributeValueDeletedEvent;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DeleteUserAttributeValueHandler
{
    private $repository;
    private $eventBus;

    public function __construct(UserAttributeValueRepositoryInterface $repository, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->eventBus = $eventBus;
    }

    public function handle(DeleteUserAttributeValueCommand $command): void
    {
        $userAttributeValue = $this->repository->find($command->userId, $command->attributeValueId);

        $this->repository->delete($userAttributeValue);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserAttributeValueDeletedEvent($userAttributeValue));
        }
    }
}
