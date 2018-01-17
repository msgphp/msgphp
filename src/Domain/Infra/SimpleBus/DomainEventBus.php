<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\EventBusInterface;
use SimpleBus\Message\Bus\MessageBus;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainEventBus implements EventBusInterface
{
    private $messageBus;

    public function __construct(MessageBus $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @param object $message
     */
    public function handle($message): void
    {
        $this->messageBus->handle($message);
    }
}
