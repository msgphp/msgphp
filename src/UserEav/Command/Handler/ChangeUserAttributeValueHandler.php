<?php

declare(strict_types=1);

namespace MsgPhp\User\Command\Handler;

use MsgPhp\Domain\DomainMessageBus;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\Command\ChangeUserAttributeValue;
use MsgPhp\User\Event\UserAttributeValueChanged;
use MsgPhp\User\Repository\UserAttributeValueRepository;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ChangeUserAttributeValueHandler
{
    private $factory;
    private $bus;
    private $repository;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus, UserAttributeValueRepository $repository)
    {
        $this->factory = $factory;
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function __invoke(ChangeUserAttributeValue $command): void
    {
        $userAttributeValue = $this->repository->find($command->attributeValueId);
        $oldValue = $userAttributeValue->getValue();
        $newValue = $command->value;

        if ($newValue === $oldValue) {
            return;
        }

        $userAttributeValue->changeValue($command->value);
        $this->repository->save($userAttributeValue);
        $this->bus->dispatch($this->factory->create(UserAttributeValueChanged::class, compact('userAttributeValue', 'oldValue', 'newValue')));
    }
}
