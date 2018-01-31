<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait MessageDispatchingTrait
{
    private $factory;
    private $bus;

    public function __construct(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus)
    {
        $this->factory = $factory;
        $this->bus = $bus;
    }

    private function dispatch(string $class, array $context = [])
    {
        return $this->bus->dispatch($this->factory->create($class, $context));
    }
}
