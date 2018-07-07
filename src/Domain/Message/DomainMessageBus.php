<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements DomainMessageBusInterface
{
    private $commandBus;
    private $eventBus;
    private $eventClasses;

    public function __construct(DomainMessageBusInterface $commandBus, DomainMessageBusInterface $eventBus, array $eventClasses)
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
        $this->eventClasses = array_flip($eventClasses);
    }

    public function dispatch($message)
    {
        if (isset($this->eventClasses[get_class($message)])) {
            return $this->eventBus->dispatch($message);
        }

        return $this->commandBus->dispatch($message);
    }
}
