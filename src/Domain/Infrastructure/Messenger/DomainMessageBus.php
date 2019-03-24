<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Messenger;

use MsgPhp\Domain\Message\DomainMessageBus as BaseDomainMessageBus;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements BaseDomainMessageBus
{
    /**
     * @var MessageBusInterface
     */
    private $commandBus;

    /**
     * @var MessageBusInterface
     */
    private $eventBus;

    /**
     * @psalm-var array<class-string, int>
     *
     * @var int[]
     */
    private $eventClasses;

    /**
     * @psalm-param array<int, class-string> $eventClasses
     *
     * @param string[] $eventClasses
     */
    public function __construct(MessageBusInterface $commandBus, MessageBusInterface $eventBus, array $eventClasses = [])
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
        $this->eventClasses = array_flip($eventClasses);
    }

    public function dispatch($message): void
    {
        if (isset($this->eventClasses[$message instanceof Envelope ? \get_class($message->getMessage()) : \get_class($message)])) {
            $this->eventBus->dispatch($message);
        } else {
            $this->commandBus->dispatch($message);
        }
    }
}
