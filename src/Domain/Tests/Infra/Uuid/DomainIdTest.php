<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Uuid;

use MsgPhp\Domain\Infra\Uuid\DomainId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

final class DomainIdTest extends TestCase
{
    private const NIL_UUID = '00000000-0000-0000-0000-000000000000';

    public function testCreateNewUuid(): void
    {
        $this->assertNotSame((string) new DomainId(), (string) new DomainId());
        $this->assertNotSame((new DomainId())->toString(), (new DomainId())->toString());
    }

    public function testInvalidUuid(): void
    {
        $this->expectException(InvalidUuidStringException::class);

        new DomainId('foo');
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse((new DomainId())->isEmpty());
        $this->assertFalse((new DomainId(self::NIL_UUID))->isEmpty());
    }

    public function testEquals(): void
    {
        $this->assertTrue(($id = new DomainId())->equals($id));
        $this->assertTrue((new DomainId(self::NIL_UUID))->equals(new DomainId(self::NIL_UUID)));
        $this->assertFalse((new DomainId())->equals(new DomainId()));
        $this->assertFalse((new DomainId())->equals(new OtherDomainId()));
        $this->assertFalse((new DomainId(self::NIL_UUID))->equals(new DomainId()));
        $this->assertFalse((new DomainId(self::NIL_UUID))->equals(new OtherDomainId()));
        $this->assertFalse((new DomainId(self::NIL_UUID))->equals(new OtherDomainId(self::NIL_UUID)));
    }

    public function testToString(): void
    {
        $this->assertSame(self::NIL_UUID, (new DomainId(self::NIL_UUID))->toString());
        $this->assertSame(self::NIL_UUID, (string) new DomainId(self::NIL_UUID));
        $this->assertNotSame((new DomainId())->toString(), (new DomainId())->toString());
        $this->assertNotSame((string) new DomainId(), (string) new DomainId());
    }

    public function testSerialize(): void
    {
        $this->assertTrue(($serialized = serialize(new DomainId())) === serialize(unserialize($serialized)));
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(json_encode(self::NIL_UUID), json_encode($this->getNilUuid()));
    }

    private function getNilUuid(): DomainId
    {
        return new DomainId(self::NIL_UUID);
    }
}

class OtherDomainId extends DomainId
{
}
