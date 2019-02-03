<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use MsgPhp\Eav\AttributeValueIdInterface;
use MsgPhp\Eav\Entity\Attribute;
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

    /**
     * @var UserAttributeValueRepositoryInterface
     */
    private $repository;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus, UserAttributeValueRepositoryInterface $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserAttributeValueCommand $command): void
    {
        $context = $command->context;
        $context['user'] = $this->factory->reference(User::class, ['id' => $command->userId]);
        $context['attributeValue'] = (array) $context['attributeValue'] ?? [];
        $context['attributeValue']['id'] = $context['attributeValue']['id'] ?? $this->factory->create(AttributeValueIdInterface::class);
        $context['attributeValue']['attribute'] = $this->factory->reference(Attribute::class, ['id' => $command->attributeId]);
        $context['attributeValue']['value'] = $command->value;
        $userAttributeValue = $this->factory->create(UserAttributeValue::class, $context);

        $this->repository->save($userAttributeValue);
        $this->dispatch(UserAttributeValueAddedEvent::class, compact('userAttributeValue', 'context'));
    }
}
