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

        self::assertTrue($object->handleEvent($event = new TestAction()));
        self::assertTrue($event->handled);

        $event = $this->getMockBuilder(DomainEvent::class)
            ->setMockClassName('MsgPhp_Test_Action')
            ->getMock()
        ;
        self::assertFalse($object->handleEvent($event));
        self::assertTrue($event->handled);
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

            private function onTestActionEvent(TestAction $event): bool
            {
                $event->handled = true;

                return true;
            }

            private function onMsgPhp_Test_ActionEvent(\MsgPhp_Test_Action $event): bool
            {
                $event->handled = true;

                return false;
            }
        };
    }
}

class TestAction implements DomainEvent
{
    public $handled = false;
}
