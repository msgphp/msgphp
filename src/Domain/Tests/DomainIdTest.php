<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainIdTest extends TestCase
{
    /**
     * @dataProvider provideIds
     */
    public function testIsEmpty(DomainId $id, bool $isEmpty): void
    {
        if ($isEmpty) {
            $this->assertTrue($id->isEmpty());
        } else {
            $this->assertFalse($id->isEmpty());
        }
    }

    public function testEquals(): void
    {
        $this->assertTrue(($id = new DomainId('foo'))->equals($id));
        $this->assertTrue((new DomainId(''))->equals(new DomainId('')));
        $this->assertTrue((new DomainId(' '))->equals(new DomainId(' ')));
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
        $this->assertSame('', (new DomainId())->toString());
        $this->assertSame('', (string) new DomainId(null));
        $this->assertSame('', (string) new DomainId(''));
        $this->assertSame(' ', (new DomainId(' '))->toString());
        $this->assertSame('foo', (string) new DomainId('foo'));
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

    public function testJsonSerializeCastsUnknownIdToNull(): void
    {
        $this->assertNull(json_decode(json_encode(new DomainId())));
    }

    public function provideIds(): iterable
    {
        yield [new DomainId(), true];
        yield [new DomainId(null), true];
        yield [new DomainId(''), false];
        yield [new DomainId(' '), false];
        yield [new DomainId('0'), false];
        yield [new DomainId('foo'), false];
    }
}

class OtherDomainId extends DomainId
{
}
