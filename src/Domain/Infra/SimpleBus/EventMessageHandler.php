<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use SimpleBus\Message\Bus\MessageBus;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EventMessageHandler
{
    private $bus;

    public function __construct(MessageBus $bus = null)
    {
        $this->bus = $bus;
    }

    /**
     * @param object $message
     */
    public function __invoke($message): void
    {
        if (null !== $this->bus) {
            $this->bus->handle($message);
        }
    }
}
