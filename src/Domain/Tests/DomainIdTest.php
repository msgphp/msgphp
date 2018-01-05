<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainIdTest extends TestCase
{
    public function testIsKnown(): void
    {
        $this->assertTrue((new DomainId(''))->isKnown());
        $this->assertTrue((new DomainId('foo'))->isKnown());
        $this->assertFalse((new DomainId())->isKnown());
        $this->assertFalse((new DomainId(null))->isKnown());
    }

    public function testEquals(): void
    {
        $this->assertTrue(($id = new DomainId('foo'))->equals($id));
        $this->assertTrue((new DomainId('foo'))->equals(new DomainId('foo')));
        $this->assertFalse((new DomainId())->equals(new DomainId()));
        $this->assertFalse((new DomainId())->equals(new OtherDomainId()));
        $this->assertFalse((new DomainId('foo'))->equals(new DomainId()));
        $this->assertFalse((new DomainId('foo'))->equals(new DomainId('bar')));
        $this->assertFalse((new DomainId('foo'))->equals(new OtherDomainId()));
        $this->assertFalse((new DomainId('foo'))->equals(new OtherDomainId('foo')));
        $this->assertFalse((new DomainId('foo'))->equals(new OtherDomainId('bar')));
    }

    public function testToString(): void
    {
        $this->assertSame('foo', (string) new DomainId('foo'));
        $this->assertSame('foo', (new DomainId('foo'))->toString());
    }

    public function testToStringWithUnknownId(): void
    {
        $id = new DomainId();

        $this->expectException(\LogicException::class);

        $id->toString();
    }

    /**
     * @dataProvider provideIds
     */
    public function testSerialize(DomainId $id): void
    {
        $this->assertEquals($id, unserialize(serialize($id)));
    }

    /**
     * @dataProvider provideIds
     */
    public function testJsonSerialize(DomainId $id): void
    {
        $this->assertEquals($id, new DomainId(json_decode(json_encode($id))));
    }

    public function provideIds(): iterable
    {
        yield [new DomainId()];
        yield [new DomainId(null)];
        yield [new DomainId('')];
        yield [new DomainId('foo')];
    }
}

class OtherDomainId extends DomainId
{
}
