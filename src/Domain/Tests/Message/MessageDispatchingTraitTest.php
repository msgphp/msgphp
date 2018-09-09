<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Message;

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Message\{DomainMessageBusInterface, MessageDispatchingTrait};
use PHPUnit\Framework\TestCase;

final class MessageDispatchingTraitTest extends TestCase
{
    public function testDispatch(): void
    {
        $factory = $this->createMock(DomainObjectFactoryInterface::class);
        $factory->expects(self::once())
            ->method('create')
            ->willReturnCallback(function ($class, $context) {
                $o = new \stdClass();
                $o->class = $class;
                $o->context = $context;

                return $o;
            });
        $bus = $this->createMock(DomainMessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->willReturnArgument(0);

        self::assertSame(['class' => 'class', 'context' => ['argument' => 'value', 1]], (array) $this->getObject($factory, $bus)->dispatch('class', ['argument' => 'value', 1]));
    }

    private function getObject(DomainObjectFactoryInterface $factory, DomainMessageBusInterface $bus)
    {
        return new class($factory, $bus) {
            use MessageDispatchingTrait {
                dispatch as public;
            }
        };
    }
}
