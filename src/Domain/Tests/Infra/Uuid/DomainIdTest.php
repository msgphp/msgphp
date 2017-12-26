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
        $this->assertNotSame((string) $this->getUuid(), (string) $this->getUuid());
        $this->assertNotSame($this->getUuid()->toString(), $this->getUuid()->toString());
    }

    public function testInvalidUuid(): void
    {
        $this->expectException(InvalidUuidStringException::class);

        $this->getUuid('foo');
    }

    public function testToString(): void
    {
        $this->assertSame(self::NIL_UUID, (string) $this->getNilUuid());
        $this->assertSame(self::NIL_UUID, $this->getNilUuid()->toString());
    }

    public function testEquals(): void
    {
        $this->assertFalse($this->getUuid()->equals($this->getNilUuid()));
        $this->assertTrue($this->getNilUuid()->equals($this->getNilUuid()));
        $this->assertFalse($this->getNilUuid()->equals($this->getUuid()));
        $this->assertTrue($this->getNilUuid()->equals($this->getNilUuid()));
        $this->assertFalse($this->getUuid()->equals($this->getUuid()));
    }

    public function testSerialize(): void
    {
        $this->assertTrue(($serialized = serialize($this->getUuid())) === serialize(unserialize($serialized)));
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(json_encode(self::NIL_UUID), json_encode($this->getNilUuid()));
    }

    private function getUuid(string $uuid = null): DomainId
    {
        return new DomainId($uuid);
    }

    private function getNilUuid(): DomainId
    {
        return $this->getUuid(self::NIL_UUID);
    }
}
