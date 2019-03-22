<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\DomainMessageBusInterface;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Eav\Attribute;
use MsgPhp\Eav\AttributeValueIdInterface;
use MsgPhp\User\Command\AddUserAttributeValueCommand;
use MsgPhp\User\Event\UserAttributeValueAddedEvent;
use MsgPhp\User\Repository\UserAttributeValueRepositoryInterface;
use MsgPhp\User\User;
use MsgPhp\User\UserAttributeValue;

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
