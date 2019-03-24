<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Console;

use MsgPhp\Domain\Message\MessageReceiving;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class MessageReceiver
{
    /**
     * @var MessageReceiving|null
     */
    private $receiver;

    /**
     * @param object $message
     */
    public function receive($message): void
    {
        if (null === $this->receiver) {
            return;
        }

        $this->receiver->onMessageReceived($message);
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->receiver = ($command = $event->getCommand()) instanceof MessageReceiving ? $command : null;
    }

    public function onTerminate(): void
    {
        $this->receiver = null;
    }
}
