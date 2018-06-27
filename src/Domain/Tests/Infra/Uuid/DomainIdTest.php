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

        $this->assertInstanceOf(DomainId::class, DomainId::fromValue(null));
        $this->assertInstanceOf(DomainId::class, DomainId::fromValue('00000000-0000-0000-0000-000000000000'));
        $this->assertNotSame((array) new DomainId(), (array) DomainId::fromValue(null));
        $this->assertNotSame((array) new DomainId(null), (array) DomainId::fromValue(null));
        $this->assertNotSame((array) new DomainId($uuid), (array) DomainId::fromValue(null));
        $this->assertSame((array) new DomainId($uuid), (array) DomainId::fromValue($uuid));
        $this->assertInstanceOf(OtherTestDomainId::class, OtherTestDomainId::fromValue(null));
    }

    public function testFromValueWithInvalidUuid(): void
    {
        $this->expectException(InvalidUuidStringException::class);

        DomainId::fromValue('00000000-0000-0000-0000-00000000000');
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse((new DomainId())->isEmpty());
        $this->assertFalse((new DomainId($this->createMock(UuidInterface::class)))->isEmpty());
    }

    public function testEquals(): void
    {
        $id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        $this->assertTrue($id->equals($id));
        $this->assertTrue($id->equals(new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'))));
        $this->assertFalse($id->equals(new DomainId()));
        $this->assertFalse($id->equals(new OtherTestDomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'))));
    }

    public function testToString(): void
    {
        $id = new DomainId(Uuid::fromString($uuid = '00000000-0000-0000-0000-000000000000'));

        $this->assertSame($uuid, $id->toString());
        $this->assertSame($uuid, (string) $id);
        $this->assertNotSame((new DomainId())->toString(), (new DomainId())->toString());
        $this->assertNotSame((string) new DomainId(), (string) new DomainId());
    }

    public function testSerialize(): void
    {
        $id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        $this->assertEquals($id->toString(), (string) unserialize(serialize($id)));
    }

    public function testJsonSerialize(): void
    {
        $id = new DomainId(Uuid::fromString('00000000-0000-0000-0000-000000000000'));

        $this->assertSame($id->toString(), json_decode((string) json_encode($id)));
    }
}

class OtherTestDomainId extends DomainId
{
}
