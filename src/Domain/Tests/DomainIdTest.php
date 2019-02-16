<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainIdTest extends TestCase
{
    public function testFromValue(): void
    {
        self::assertSame((array) new DomainId(), (array) DomainId::fromValue(null));
        self::assertSame((array) new DomainId(null), (array) DomainId::fromValue(null));
        self::assertSame((array) new DomainId('foo'), (array) DomainId::fromValue('foo'));
        self::assertSame((array) new DomainId(' '), (array) DomainId::fromValue(' '));
        self::assertSame((array) new DomainId('1'), (array) DomainId::fromValue(1));
        self::assertInstanceOf(OtherTestDomainId::class, OtherTestDomainId::fromValue(null));
    }

    public function testEmptyIdValue(): void
    {
        $this->expectException(\LogicException::class);

        new DomainId('');
    }

    public function testIsEmpty(): void
    {
        self::assertTrue((new DomainId())->isEmpty());
        self::assertTrue((new DomainId(null))->isEmpty());
        self::assertFalse((new DomainId('foo'))->isEmpty());
        self::assertFalse((new DomainId(' '))->isEmpty());
    }

    public function testEquals(): void
    {
        $id = new DomainId('foo');
        $emptyId = new DomainId();

        self::assertTrue($id->equals($id));
        self::assertTrue($id->equals(new DomainId('foo')));
        self::assertFalse($id->equals($emptyId));
        self::assertFalse($id->equals(new OtherTestDomainId('foo')));
        self::assertTrue($emptyId->equals($emptyId));
        self::assertFalse($emptyId->equals(new DomainId()));
        self::assertFalse($emptyId->equals(new OtherTestDomainId()));
    }

    /**
     * @dataProvider provideIds
     */
    public function testToString(DomainId $id, string $value): void
    {
        self::assertSame($value, $id->toString());
        self::assertSame($value, (string) $id);
    }

    /**
     * @dataProvider provideIds
     */
    public function testSerialize(DomainId $id): void
    {
        self::assertSame((array) $id, (array) unserialize(serialize($id)));
    }

    /**
     * @dataProvider provideIds
     */
    public function testJsonSerialize(DomainId $id): void
    {
        self::assertSame($id->isEmpty() ? null : $id->toString(), json_decode((string) json_encode($id)));
    }

    public function provideIds(): iterable
    {
        yield [new DomainId(), ''];
        yield [new DomainId(null), ''];
        yield [new DomainId('foo'), 'foo'];
        yield [new DomainId(' '), ' '];
    }
}

class OtherTestDomainId extends DomainId
{
}
