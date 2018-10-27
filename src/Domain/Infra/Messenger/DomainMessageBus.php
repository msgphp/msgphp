<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Messenger;

use MsgPhp\Domain\Message\DomainMessageBusInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements DomainMessageBusInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch($message): void
    {
        $this->bus->dispatch($message);
    }
}
