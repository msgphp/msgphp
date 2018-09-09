<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\SimpleBus;

use MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\MessageBus;

final class DomainMessageBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $bus = $this->createMock(MessageBus::class);
        $bus->expects(self::once())
            ->method('handle')
            ->with($message = new \stdClass())
            ->willReturn('foo');

        self::assertNull((new DomainMessageBus($bus))->dispatch($message));
    }
}
