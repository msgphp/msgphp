<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console\Event;

use MsgPhp\Domain\Message\MessageReceivingInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class MessageSubscriber
{
    /** @var MessageReceivingInterface|null */
    private $receiver;

    /**
     * @param object $message
     */
    public function __invoke($message): void
    {
        if (null === $this->receiver) {
            return;
        }

        $this->receiver->onMessageReceived($message);
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->receiver = ($command = $event->getCommand()) instanceof MessageReceivingInterface ? $command : null;
    }

    public function onTerminate(): void
    {
        $this->receiver = null;
    }
}
