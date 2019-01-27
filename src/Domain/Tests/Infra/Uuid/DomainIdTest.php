<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Uuid;

use MsgPhp\Domain\Infra\Uuid\DomainId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class DomainIdTest extends TestCase
{
    public function testFromValue(): void
    {
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000000');

        self::assertInstanceOf(DomainId::class, DomainId::fromValue(null));
        self::assertInstanceOf(DomainId::class, DomainId::fromValue('00000000-0000-0000-0000-000000000000'));
        self::assertNotSame((array) new DomainId(), (array) DomainId::fromValue(null));
        self::assertNotSame((array) new DomainId(null), (array) DomainId::fromValue(null));
        self::assertNotSame((array) new DomainId($uuid), (array) DomainId::fromValue(null));
        self::assertSame((array) new DomainId($uuid), (array) DomainId::fromValue($uuid));
        self::assertInstanceOf(OtherTestDomainId::class, OtherTestDomainId::fromValue(null));
    }

    public function testFromValueWithInvalidUuid(): void
    {
        $this->expectException(InvalidUuidStringException::class);

        DomainId::fromValue('00000000-0000-0000-0000-00000000000');
    }

    public function testIsEmpty(): void
    {
        self::assertFalse((new DomainId())->isEmpty());
        self::assertFalse((new DomainId($this->createMock(UuidInterface::class)))->isEmpty());
    }

    public function testEquals(): void
    {
        $id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        self::assertTrue($id->equals($id));
        self::assertTrue($id->equals(new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'))));
        self::assertFalse($id->equals(new DomainId()));
        self::assertFalse($id->equals(new OtherTestDomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'))));
    }

    public function testToString(): void
    {
        $id = new DomainId(Uuid::fromString($uuid = '00000000-0000-0000-0000-000000000000'));

        self::assertSame($uuid, $id->toString());
        self::assertSame($uuid, (string) $id);
        self::assertNotSame((new DomainId())->toString(), (new DomainId())->toString());
        self::assertNotSame((string) new DomainId(), (string) new DomainId());
    }

    public function testSerialize(): void
    {
        $id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        self::assertSame($id->toString(), (string) unserialize(serialize($id)));
    }

    public function testJsonSerialize(): void
    {
        $id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        self::assertSame($id->toString(), json_decode((string) json_encode($id)));
    }
}

class OtherTestDomainId extends DomainId
{
}
