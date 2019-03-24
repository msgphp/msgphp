<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

use MsgPhp\Domain\Factory\DomainObjectFactory;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait MessageDispatchingTrait
{
    /**
     * @var DomainObjectFactory
     */
    private $factory;

    /**
     * @var DomainMessageBus
     */
    private $bus;

    public function __construct(DomainObjectFactory $factory, DomainMessageBus $bus)
    {
        $this->factory = $factory;
        $this->bus = $bus;
    }

    /**
     * @psalm-param class-string $class
     */
    private function dispatch(string $class, array $context = []): void
    {
        $this->bus->dispatch($this->factory->create($class, $context));
    }
}
