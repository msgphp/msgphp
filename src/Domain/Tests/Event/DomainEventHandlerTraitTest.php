<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Event;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use PHPUnit\Framework\TestCase;

final class DomainEventHandlerTraitTest extends TestCase
{
    public function testHandleEvent(): void
    {
        $object = $this->getObject();

        self::assertTrue($object->handleEvent($event = new TestEvent()));
        self::assertTrue($event->handled);
        self::assertTrue($object->handleEvent($event = new TestEventDifferentSuffix()));
        self::assertTrue($event->handled);

        $event = $this->getMockBuilder(DomainEvent::class)
            ->setMockClassName('MsgPhp_Test_Root_Event')
            ->getMock()
        ;
        self::assertFalse($object->handleEvent($event));
    }

    public function testHandleEventWithUnknownEvent(): void
    {
        $object = $this->getObject();

        $this->expectException(\LogicException::class);

        $object->handleEvent($this->createMock(DomainEvent::class));
    }

    private function getObject(): DomainEventHandler
    {
        return new class() implements DomainEventHandler {
            use DomainEventHandlerTrait;

            private function handleTestEvent(TestEvent $event): bool
            {
                $event->handled = true;

                return true;
            }

            private function handleTestEventDifferentSuffixEvent(TestEventDifferentSuffix $event): bool
            {
                $event->handled = true;

                return true;
            }

            private function handleMsgPhp_Test_Root_Event(DomainEvent $event): bool
            {
                return false;
            }
        };
    }
}

class TestEvent implements DomainEvent
{
    public $handled = false;
}

class TestEventDifferentSuffix extends TestEvent
{
}
