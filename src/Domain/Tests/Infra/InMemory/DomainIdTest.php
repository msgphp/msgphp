<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Infra\InMemory\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainIdTest extends TestCase
{
    public function testCreateNewId(): void
    {
        $this->assertNotSame((string) $this->getId(), (string) $this->getId());
        $this->assertNotSame($this->getId()->toString(), $this->getId()->toString());
    }

    public function testToString(): void
    {
        $this->assertSame('foo', (string) $this->getId('foo'));
        $this->assertSame('foo', $this->getId('foo')->toString());
    }

    public function testEquals(): void
    {
        $this->assertFalse($this->getId()->equals($this->getId()));
        $this->assertTrue($this->getId('foo')->equals($this->getId('foo')));
        $this->assertFalse($this->getId('foo')->equals($this->getId()));
    }

    public function testSerialize(): void
    {
        $this->assertTrue(($serialized = serialize($this->getId())) === serialize(unserialize($serialized)));
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(json_encode('foo'), json_encode($this->getId('foo')));
    }

    private function getId(string $id = null): DomainId
    {
        return new DomainId($id);
    }
}
