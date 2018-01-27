<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\DomainMessageBusInterface;
use Psr\Log\LoggerInterface;
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
    private $logger;

    public function __construct(MessageBus $bus, MessageBus $eventBus = null, LoggerInterface $logger = null)
    {
        $this->bus = $bus;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    public function dispatch($message)
    {
        try {
            $this->bus->handle($message);
        } catch (UndefinedCallable $e) {
            if (null !== $this->eventBus) {
                $this->eventBus->handle($message);
            } elseif (null !== $this->logger) {
                $this->logger->warning('Unable to handle message "{class}"', [
                    'class' => get_class($message),
                    'message' => $message,
                    'exception' => $e,
                ]);
            }
        }

        return null;
    }
}
