<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Message;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class FallbackMessageHandler
{
    private $bus;

    public function __construct(DomainMessageBusInterface $bus = null)
    {
        $this->bus = $bus;
    }

    /**
     * @param object $message
     */
    public function __invoke($message)
    {
        return null === $this->bus ? null : $this->bus->dispatch($message);
    }
}
