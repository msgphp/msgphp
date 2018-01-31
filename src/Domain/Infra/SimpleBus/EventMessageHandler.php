<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use MsgPhp\Domain\Message\MessageReceivingInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EventMessageHandler
{
    private $bus;

    /** @var MessageReceivingInterface|null */
    private $receiver;

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

        if (null !== $this->receiver) {
            $this->receiver->onMessageReceived($message);
        }
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->receiver = ($command = $event->getCommand()) instanceof MessageReceivingInterface ? $command : null;
    }

    public function onConsoleTerminate(): void
    {
        $this->receiver = null;
    }
}
