<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements DomainMessageBusInterface
{
    private $bus;
    private $eventBus;
    private $eventClasses;

    public function __construct(DomainMessageBusInterface $bus, DomainMessageBusInterface $eventBus, array $eventClasses)
    {
        $this->bus = $bus;
        $this->eventBus = $eventBus;
        $this->eventClasses = array_flip($eventClasses);
    }

    public function dispatch($message)
    {
        if (isset($this->eventClasses[$class = get_class($message)])) {
            return $this->eventBus->dispatch($message);
        }

        return $this->bus->dispatch($message);
    }
}
