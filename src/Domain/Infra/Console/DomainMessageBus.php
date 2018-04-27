<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Console;

use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageReceivingInterface};
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainMessageBus implements DomainMessageBusInterface
{
    private $bus;

    /** @var MessageReceivingInterface|null */
    private $receiver;

    public function __construct(DomainMessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch($message)
    {
        if (null !== $this->receiver) {
            $this->receiver->onMessageReceived($message);
        }

        return $this->bus->dispatch($message);
    }

    /**
     * @internal
     */
    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->receiver = ($command = $event->getCommand()) instanceof MessageReceivingInterface ? $command : null;
    }

    /**
     * @internal
     */
    public function onTerminate(): void
    {
        $this->receiver = null;
    }
}
