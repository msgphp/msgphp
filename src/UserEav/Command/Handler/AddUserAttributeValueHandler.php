<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use MsgPhp\Eav\Attribute;
use MsgPhp\Eav\AttributeValueId;
use MsgPhp\User\Command\AddUserAttributeValue;
use MsgPhp\User\Event\UserAttributeValueAdded;
use MsgPhp\User\Repository\UserAttributeValueRepository;
use MsgPhp\User\User;
use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserAttributeValueHandler
{
    use MessageDispatchingTrait;

    /**
     * @var UserAttributeValueRepository
     */
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserAttributeValueRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(AddUserAttributeValue $command): void
    {
        $context = $command->context;
        $context['user'] = $this->factory->reference(User::class, ['id' => $command->userId]);
        $context['attributeValue'] = (array) $context['attributeValue'] ?? [];
        $context['attributeValue']['id'] = $context['attributeValue']['id'] ?? $this->factory->create(AttributeValueId::class);
        $context['attributeValue']['attribute'] = $this->factory->reference(Attribute::class, ['id' => $command->attributeId]);
        $context['attributeValue']['value'] = $command->value;
        $userAttributeValue = $this->factory->create(UserAttributeValue::class, $context);

        $this->repository->save($userAttributeValue);
        $this->dispatch(UserAttributeValueAdded::class, compact('userAttributeValue', 'context'));
    }
}
