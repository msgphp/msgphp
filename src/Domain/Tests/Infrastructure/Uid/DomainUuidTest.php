<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Uid;

use MsgPhp\Domain\GenericDomainId;
use MsgPhp\Domain\Infrastructure\Uid\DomainUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
final class DomainUuidTest extends TestCase
{
    public function testFromString(): void
    {
        self::assertSame(Uuid::NIL, DomainUuid::fromString(null)->toString());
        self::assertSame(Uuid::NIL, DomainUuid::fromString(Uuid::NIL)->toString());
    }

    public function testFromStringWithInvalidUuid(): void
    {
        $this->expectException(\LogicException::class);

        DomainUuid::fromString('');
    }

    public function testIsNil(): void
    {
        self::assertFalse((new DomainUuid())->isNil());
        self::assertTrue((new DomainUuid(Uuid::fromString(Uuid::NIL)))->isNil());
    }

    public function testEquals(): void
    {
        $id = new DomainUuid(Uuid::fromString(Uuid::NIL));

        self::assertTrue($id->equals($id));
        self::assertTrue($id->equals(new DomainUuid(Uuid::fromString(Uuid::NIL))));
        self::assertFalse($id->equals(new DomainUuid()));
        self::assertFalse($id->equals(Uuid::NIL));
        self::assertFalse($id->equals(new \stdClass()));
        self::assertFalse($id->equals(new GenericDomainId(Uuid::NIL)));
    }

    public function testToString(): void
    {
        $id = new DomainUuid(Uuid::fromString(Uuid::NIL));

        self::assertSame(Uuid::NIL, $id->toString());
        self::assertSame(Uuid::NIL, (string) $id);
        self::assertNotSame((new DomainUuid())->toString(), (new DomainUuid())->toString());
        self::assertNotSame((string) new DomainUuid(), (string) new DomainUuid());
    }

    public function testSerialize(): void
    {
        $id = new DomainUuid(Uuid::fromString(Uuid::NIL));

        self::assertSame($id->toString(), (string) unserialize(serialize($id)));
    }
}
