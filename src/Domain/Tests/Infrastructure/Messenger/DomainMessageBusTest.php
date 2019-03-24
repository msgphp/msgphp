<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Messenger;

use MsgPhp\Domain\Infrastructure\Messenger\DomainMessageBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class DomainMessageBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $commandBus = $this->createMock(MessageBusInterface::class);
        $commandBus->expects(self::once())
            ->method('dispatch')
            ->with($commandMessage = new TestCommandMessage())
            ->willReturn(new Envelope($commandMessage))
        ;
        $eventBus = $this->createMock(MessageBusInterface::class);
        $eventBus->expects(self::once())
            ->method('dispatch')
            ->with($eventMessage = new TestEventMessage())
            ->willReturn(new Envelope($eventMessage))
        ;

        $bus = new DomainMessageBus($commandBus, $eventBus, [TestEventMessage::class]);
        $bus->dispatch($commandMessage);
        $bus->dispatch($eventMessage);
    }
}

final class TestCommandMessage
{
}

final class TestEventMessage
{
}
