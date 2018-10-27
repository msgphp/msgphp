<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\Message\DomainMessageBusInterface;
use SimpleBus\Message\Bus\MessageBus;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements DomainMessageBusInterface
{
    private $bus;

    public function __construct(MessageBus $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch($message): void
    {
        $this->bus->handle($message);
    }
}
