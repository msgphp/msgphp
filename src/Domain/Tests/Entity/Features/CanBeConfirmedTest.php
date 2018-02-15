<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\CanBeConfirmed;
use MsgPhp\Domain\Event\ConfirmEvent;
use PHPUnit\Framework\TestCase;

final class CanBeConfirmedTest extends TestCase
{
    public function testConfirm(): void
    {
        $object = $this->getObject('foo', null);

        $this->assertSame('foo', $object->getConfirmationToken());
        $this->assertNull($object->getConfirmedAt());
        $this->assertFalse($object->isConfirmed());

        $object->confirm();

        $this->assertNull($object->getConfirmationToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $object->getConfirmedAt());
        $this->assertTrue($object->isConfirmed());
    }

    public function testHandleConfirmEvent(): void
    {
        $object = $this->getObject('foo', null);

        $this->assertTrue($object->handleConfirmEvent($this->createMock(ConfirmEvent::class)));
        $this->assertNull($prevToken = $object->getConfirmationToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $object->getConfirmedAt());
        $this->assertTrue($object->isConfirmed());
        $this->assertFalse($object->handleConfirmEvent($this->createMock(ConfirmEvent::class)));
        $this->assertTrue($object->isConfirmed());
    }

    private function getObject($confirmationToken, $confirmedAt)
    {
        return new class($confirmationToken, $confirmedAt) {
            use CanBeConfirmed {
                handleConfirmEvent as public;
            }

            public function __construct($confirmationToken, $confirmedAt)
            {
                $this->confirmationToken = $confirmationToken;
                $this->confirmedAt = $confirmedAt;
            }
        };
    }
}
