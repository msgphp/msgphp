<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainIdTest extends TestCase
{
    public function testFromValue(): void
    {
        $this->assertSame((array) new DomainId(), (array) DomainId::fromValue(null));
        $this->assertSame((array) new DomainId(null), (array) DomainId::fromValue(null));
        $this->assertNotSame((array) new DomainId(''), (array) DomainId::fromValue(null));
        $this->assertSame((array) new DomainId(''), (array) DomainId::fromValue(''));
        $this->assertSame((array) new DomainId(''), (array) DomainId::fromValue(false));
        $this->assertSame((array) new DomainId('1'), (array) DomainId::fromValue(1));
        $this->assertInstanceOf(OtherTestDomainId::class, OtherTestDomainId::fromValue(null));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue((new DomainId())->isEmpty());
        $this->assertTrue((new DomainId(null))->isEmpty());
        $this->assertFalse((new DomainId(''))->isEmpty());
        $this->assertFalse((new DomainId(' '))->isEmpty());
        $this->assertFalse((new DomainId('foo'))->isEmpty());
    }

    public function testEquals(): void
    {
        $id = new DomainId('foo');
        $emptyId = new DomainId();

        $this->assertTrue($id->equals($id));
        $this->assertTrue($id->equals(new DomainId('foo')));
        $this->assertFalse($id->equals($emptyId));
        $this->assertFalse($id->equals(new OtherTestDomainId('foo')));
        $this->assertTrue($emptyId->equals($emptyId));
        $this->assertFalse($emptyId->equals(new DomainId()));
        $this->assertFalse($emptyId->equals(new OtherTestDomainId()));
    }

    /**
     * @dataProvider provideIds
     */
    public function testToString(DomainId $id, string $value): void
    {
        $this->assertSame($value, $id->toString());
        $this->assertSame($value, (string) $id);
    }

    /**
     * @dataProvider provideIds
     */
    public function testSerialize(DomainId $id): void
    {
        $this->assertSame((array) $id, (array) unserialize(serialize($id)));
    }

    /**
     * @dataProvider provideIds
     */
    public function testJsonSerialize(DomainId $id): void
    {
        $this->assertSame($id->isEmpty() ? null : $id->toString(), json_decode(json_encode($id)));
    }

    public function provideIds(): iterable
    {
        yield [new DomainId(), ''];
        yield [new DomainId(null), ''];
        yield [new DomainId(''), ''];
        yield [new DomainId(' '), ' '];
        yield [new DomainId('foo'), 'foo'];
    }
}

class OtherTestDomainId extends DomainId
{
}
