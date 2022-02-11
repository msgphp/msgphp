<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Event;

use MsgPhp\Domain\Event\DomainEvent;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DomainEventHandlerTraitTest extends TestCase
{
    public function testHandleEvent(): void
    {
        self::assertTrue((new TestDomainEventHandler())->handleEvent(new TestAction()));
    }

    public function testHandleRootEvent(): void
    {
        /** @var DomainEvent&MockObject $event */
        $event = $this->getMockBuilder(DomainEvent::class)
            ->setMockClassName('MsgPhp_Test_Action')
            ->getMock()
        ;

        self::assertFalse((new TestDomainEventHandler())->handleEvent($event));
    }

    public function testHandleEventWithUnknownEvent(): void
    {
        $handler = new TestDomainEventHandler();

        $this->expectException(\LogicException::class);

        $handler->handleEvent($this->createMock(DomainEvent::class));
    }
}

class TestAction implements DomainEvent
{
}

class TestDomainEventHandler implements DomainEventHandler
{
    use DomainEventHandlerTrait;

    private function onTestActionEvent(TestAction $event): bool
    {
        return true;
    }

    /**
     * @psalm-suppress UndefinedClass
     * @phpstan-ignore-next-line
     */
    private function onMsgPhp_Test_ActionEvent(\MsgPhp_Test_Action $event): bool
    {
        return false;
    }
}
