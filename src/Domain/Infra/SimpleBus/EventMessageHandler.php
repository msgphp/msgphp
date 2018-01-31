<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\SimpleBus;

use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class EventMessageHandler
{
    private $bus;
    private $collect = false;
    private $collected = [];

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

        if ($this->collect) {
            $this->collected[] = $message;
        }
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->collect = true;
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        dump($this->collected);

        $this->collect = false;
    }
}
