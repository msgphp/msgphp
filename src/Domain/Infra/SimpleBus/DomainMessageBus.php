<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\DomainMessageBusInterface;
use SimpleBus\Message\Bus\MessageBus;
use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\Exception\UndefinedCallable;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements DomainMessageBusInterface
{
    private $bus;
    private $eventBus;
    private $callableMap;
    private $isEvent = [];

    public function __construct(MessageBus $bus, MessageBus $eventBus = null, CallableMap $callableMap = null)
    {
        $this->bus = $bus;
        $this->eventBus = $eventBus;
        $this->callableMap = $callableMap;
    }

    public function dispatch($message)
    {
        if (null !== $this->eventBus && $this->isEvent($message)) {
            $this->eventBus->handle($message);

            return null;
        }

        $this->bus->handle($message);

        return null;
    }

    /**
     * @param object $message
     */
    private function isEvent($message): bool
    {
        if (isset($this->isEvent[$class = get_class($message)])) {
            return $this->isEvent[$class];
        }

        if (null === $this->callableMap) {
            return $this->isEvent[$class] = true;
        }

        try {
            $this->callableMap->get($class);

            return $this->isEvent[$class] = false;
        } catch (UndefinedCallable $e) {
            return $this->isEvent[$class] = true;
        }
    }
}
