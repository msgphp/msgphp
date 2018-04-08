<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Command;

use MsgPhp\Domain\Message\DomainMessageBusInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EventMessageCommandHandler
{
    private $eventBus;

    public function __construct(DomainMessageBusInterface $eventBus = null)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param object $message
     */
    public function __invoke($message): void
    {
        if (null === $this->eventBus) {
            return;
        }

        $this->eventBus->dispatch($message);
    }
}
