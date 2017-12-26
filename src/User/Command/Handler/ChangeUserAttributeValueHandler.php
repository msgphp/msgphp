<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Entity\EntityFactoryInterface;
use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Eav\Repository\AttributeRepositoryInterface;
use MsgPhp\User\Command\ChangeUserAttributeValueCommand;
use MsgPhp\User\Event\UserAttributeValueChangedEvent;
use MsgPhp\User\Repository\{UserAttributeValueRepositoryInterface, UserRepositoryInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserAttributeValueHandler
{
    private $repository;
    private $userRepository;
    private $attributeRepository;
    private $factory;
    private $eventBus;

    public function __construct(UserAttributeValueRepositoryInterface $repository, UserRepositoryInterface $userRepository, AttributeRepositoryInterface $attributeRepository, EntityFactoryInterface $factory, EventBusInterface $eventBus = null)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->attributeRepository = $attributeRepository;
        $this->factory = $factory;
        $this->eventBus = $eventBus;
    }

    public function handle(ChangeUserAttributeValueCommand $command): void
    {
        $userAttributeValue = $this->repository->find($command->userId, $command->attributeValueId);

        if ($command->value === $oldValue = $userAttributeValue->getValue()) {
            return;
        }

        $userAttributeValue->getAttributeValue()->changeValue($command->value);

        $this->repository->save($userAttributeValue);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserAttributeValueChangedEvent($userAttributeValue, $oldValue));
        }
    }
}
