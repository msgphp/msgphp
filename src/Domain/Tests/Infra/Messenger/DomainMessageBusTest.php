<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Messenger;

use MsgPhp\Domain\Infra\Messenger\DomainMessageBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

final class DomainMessageBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($message = new \stdClass())
            ->willReturn('foo');

        $this->assertSame('foo', (new DomainMessageBus($bus))->dispatch($message));
    }
}
