<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Messenger;

use MsgPhp\Domain\DomainMessageBus as BaseDomainMessageBus;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements BaseDomainMessageBus
{
    private $commandBus;
    private $eventBus;
    /** @var array<int, class-string> */
    private $eventClasses;
    /** @var array<class-string, int>|null */
    private $eventClassMap;

    /**
     * @param array<int, class-string> $eventClasses
     */
    public function __construct(MessageBusInterface $commandBus, MessageBusInterface $eventBus, array $eventClasses = [])
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
        $this->eventClasses = $eventClasses;
    }

    public function dispatch(object $message): void
    {
        if (null === $this->eventClassMap) {
            $this->eventClassMap = array_flip($this->eventClasses);
        }

        if (isset($this->eventClassMap[$message instanceof Envelope ? \get_class($message->getMessage()) : \get_class($message)])) {
            $this->eventBus->dispatch($message);

            return;
        }

        $this->commandBus->dispatch($message);
    }
}
