<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Messenger;

use MsgPhp\Domain\Infra\Console\MessageReceiver;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ConsoleMessageReceiverBus implements MessageBusInterface
{
    private $bus;
    private $receiver;

    public function __construct(MessageBusInterface $bus, MessageReceiver $receiver)
    {
        $this->bus = $bus;
        $this->receiver = $receiver;
    }

    public function dispatch($message)
    {
        $this->receiver->receive($message);

        return $this->bus->dispatch($message);
    }
}
