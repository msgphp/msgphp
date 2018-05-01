<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Eav\Entity\{Attribute, AttributeValue};
use MsgPhp\User\Command\AddUserAttributeValueCommand;
use MsgPhp\User\Entity\{User, UserAttributeValue};
use MsgPhp\User\Event\UserAttributeValueAddedEvent;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserAttributeValueHandler
{
    use MessageDispatchingTrait;

    private $repository;

    public function __construct(EntityAwareFactoryInterface $factory, DomainMessageBusInterface $bus, UserAttributeValueRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserAttributeValueCommand $command): void
    {
        $attributeValueContext = $command->context['attributeValue'] ?? $command->context['attribute_value'] ?? [];
        $attributeValueContext += ['id' => $this->factory->nextIdentifier(AttributeValue::class)];
        $userAttributeValue = $this->factory->create(UserAttributeValue::class, [
            'user' => $this->factory->reference(User::class, $this->factory->identify(User::class, $command->userId)),
            'attributeValue' => [
                'attribute' => $this->factory->reference(Attribute::class, $this->factory->identify(Attribute::class, $command->attributeId)),
                'value' => $command->value,
            ] + $attributeValueContext,
        ] + $command->context);

        $this->repository->save($userAttributeValue);
        $this->dispatch(UserAttributeValueAddedEvent::class, [$userAttributeValue]);
    }
}
