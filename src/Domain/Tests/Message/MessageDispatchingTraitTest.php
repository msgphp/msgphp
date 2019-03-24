<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Message;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Message\DomainMessageBus;
use MsgPhp\Domain\Message\MessageDispatchingTrait;
use PHPUnit\Framework\TestCase;

final class MessageDispatchingTraitTest extends TestCase
{
    public function testDispatch(): void
    {
        $factory = $this->createMock(DomainObjectFactory::class);
        $factory->expects(self::once())
            ->method('create')
            ->with('class', ['context'])
            ->willReturn($message = new \stdClass())
        ;
        $bus = $this->createMock(DomainMessageBus::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with($message)
        ;

        self::assertNull($this->getObject($factory, $bus)->dispatch('class', ['context']));
    }

    /**
     * @return object
     */
    private function getObject(DomainObjectFactory $factory, DomainMessageBus $bus)
    {
        return new class($factory, $bus) {
            use MessageDispatchingTrait {
                dispatch as public;
            }
        };
    }
}
