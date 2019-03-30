<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Model;

use MsgPhp\Domain\Event\Confirm;
use MsgPhp\Domain\Model\CanBeConfirmed;
use PHPUnit\Framework\TestCase;

final class CanBeConfirmedTest extends TestCase
{
    public function testConfirm(): void
    {
        $object = $this->getObject('foo', null);

        self::assertSame('foo', $object->getConfirmationToken());
        self::assertNull($object->getConfirmedAt());
        self::assertFalse($object->isConfirmed());

        $object->confirm();

        self::assertNull($object->getConfirmationToken());
        self::assertInstanceOf(\DateTimeImmutable::class, $object->getConfirmedAt());
        self::assertTrue($object->isConfirmed());
    }

    public function testOnConfirmEvent(): void
    {
        $object = $this->getObject('foo', null);

        self::assertTrue($object->onConfirmEvent(new Confirm()));
        self::assertNull($prevToken = $object->getConfirmationToken());
        self::assertInstanceOf(\DateTimeImmutable::class, $object->getConfirmedAt());
        self::assertTrue($object->isConfirmed());
        self::assertFalse($object->onConfirmEvent(new Confirm()));
        self::assertTrue($object->isConfirmed());
    }

    /**
     * @return object
     */
    private function getObject($confirmationToken, $confirmedAt)
    {
        return new class($confirmationToken, $confirmedAt) {
            use CanBeConfirmed {
                onConfirmEvent as public;
            }

            public function __construct($confirmationToken, $confirmedAt)
            {
                $this->confirmationToken = $confirmationToken;
                $this->confirmedAt = $confirmedAt;
            }
        };
    }
}
