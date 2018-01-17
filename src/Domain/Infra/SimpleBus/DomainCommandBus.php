<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\CommandBusInterface;
use SimpleBus\Message\Bus\MessageBus;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCommandBus implements CommandBusInterface
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
