<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\EventBusInterface;
use MsgPhp\Domain\Factory\EntityFactoryInterface;
use MsgPhp\Eav\Entity\AttributeValue;
use MsgPhp\Eav\Repository\AttributeRepositoryInterface;
use MsgPhp\User\Command\AddUserAttributeValueCommand;
use MsgPhp\User\Entity\UserAttributeValue;
use MsgPhp\User\Event\UserAttributeValueAddedEvent;
use MsgPhp\User\Repository\{UserAttributeValueRepositoryInterface, UserRepositoryInterface};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserAttributeValueHandler
{
    private $userAttributeValueRepository;
    private $userRepository;
    private $attributeRepository;
    private $factory;
    private $eventBus;

    public function __construct(UserAttributeValueRepositoryInterface $userAttributeValueRepository, UserRepositoryInterface $userRepository, AttributeRepositoryInterface $attributeRepository, EntityFactoryInterface $factory, EventBusInterface $eventBus = null)
    {
        $this->userAttributeValueRepository = $userAttributeValueRepository;
        $this->userRepository = $userRepository;
        $this->attributeRepository = $attributeRepository;
        $this->factory = $factory;
        $this->eventBus = $eventBus;
    }

    public function handle(AddUserAttributeValueCommand $command): void
    {
        $userAttributeValue = $this->factory->create(UserAttributeValue::class, array_replace_recursive($command->context, [
            'user' => $this->userRepository->find($command->userId),
            'attribute_value' => [
                'id' => $this->factory->nextIdentity(AttributeValue::class),
                'attribute' => $this->attributeRepository->find($command->attributeId),
                'value' => $command->value,
            ],
        ]));

        $this->userAttributeValueRepository->save($userAttributeValue);

        if (null !== $this->eventBus) {
            $this->eventBus->handle(new UserAttributeValueAddedEvent($userAttributeValue));
        }
    }
}
