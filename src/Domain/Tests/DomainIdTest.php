<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;
use MsgPhp\Domain\Tests\Fixtures\TestOtherDomainId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DomainIdTest extends TestCase
{
    public function testFromValue(): void
    {
        self::assertSame((array) new TestDomainId(), (array) TestDomainId::fromValue(null));
        self::assertSame((array) new TestDomainId(null), (array) TestDomainId::fromValue(null));
        self::assertSame((array) new TestDomainId('foo'), (array) TestDomainId::fromValue('foo'));
        self::assertSame((array) new TestDomainId('1'), (array) TestDomainId::fromValue(1));
        self::assertSame((array) new TestDomainId(' '), (array) TestDomainId::fromValue(' '));
        self::assertNotSame(TestDomainId::fromValue('1'), TestDomainId::fromValue('1'));
        self::assertInstanceOf(TestOtherDomainId::class, TestOtherDomainId::fromValue(null));
    }

    public function testFromInvalidValue(): void
    {
        $this->expectException(\LogicException::class);

        TestDomainId::fromValue(true);
    }

    public function testEmptyIdValue(): void
    {
        $this->expectException(\LogicException::class);

        new TestDomainId('');
    }

    public function testIsEmpty(): void
    {
        self::assertTrue((new TestDomainId())->isEmpty());
        self::assertTrue((new TestDomainId(null))->isEmpty());
        self::assertFalse((new TestDomainId('foo'))->isEmpty());
        self::assertFalse((new TestDomainId(' '))->isEmpty());
    }

    public function testEquals(): void
    {
        $id = new TestDomainId('foo');
        $emptyId = new TestDomainId();

        self::assertTrue($id->equals($id));
        self::assertTrue($id->equals(new TestDomainId('foo')));
        self::assertFalse($id->equals($emptyId));
        self::assertFalse($id->equals(new TestOtherDomainId('foo')));
        self::assertFalse($id->equals('foo'));
        self::assertFalse($id->equals(new \stdClass()));
        self::assertTrue($emptyId->equals($emptyId));
        self::assertTrue($emptyId->equals(new TestDomainId()));
        self::assertFalse($emptyId->equals(new TestOtherDomainId()));
        self::assertFalse($emptyId->equals(''));
        self::assertFalse($emptyId->equals(new \stdClass()));
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

    public function provideIds(): iterable
    {
        yield [new TestDomainId(), ''];
        yield [new TestDomainId(null), ''];
        yield [new TestDomainId('foo'), 'foo'];
        yield [new TestDomainId(' '), ' '];
    }
}
