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
        self::assertInstanceOf(TestDomainId::class, TestDomainId::fromValue(null));
        self::assertInstanceOf(TestOtherDomainId::class, TestOtherDomainId::fromValue(''));
        self::assertSame((array) new TestDomainId(), (array) TestDomainId::fromValue(null));
        self::assertSame((array) new TestDomainId(null), (array) TestDomainId::fromValue(null));
        self::assertSame((array) new TestDomainId(''), (array) TestDomainId::fromValue(null));
        self::assertSame((array) new TestDomainId('foo'), (array) TestDomainId::fromValue('foo'));
        self::assertSame((array) new TestDomainId('1'), (array) TestDomainId::fromValue(1));
        self::assertSame((array) new TestDomainId(' '), (array) TestDomainId::fromValue(' '));
    }

    public function testFromInvalidValue(): void
    {
        $this->expectException(\LogicException::class);

        TestDomainId::fromValue(true);
    }

    public function testIsNil(): void
    {
        self::assertTrue((new TestDomainId())->isNil());
        self::assertTrue((new TestDomainId(null))->isNil());
        self::assertTrue((new TestDomainId(''))->isNil());
        self::assertFalse((new TestDomainId(' '))->isNil());
        self::assertFalse((new TestDomainId('foo'))->isNil());
    }

    public function testEquals(): void
    {
        $id = new TestDomainId('foo');
        $nilId = new TestDomainId();

        self::assertTrue($id->equals($id));
        self::assertTrue($id->equals(new TestDomainId('foo')));
        self::assertFalse($id->equals($nilId));
        self::assertFalse($id->equals(new TestOtherDomainId('foo')));
        self::assertFalse($id->equals('foo'));
        self::assertFalse($id->equals(new \stdClass()));
        self::assertTrue($nilId->equals($nilId));
        self::assertTrue($nilId->equals(new TestDomainId()));
        self::assertFalse($nilId->equals(new TestOtherDomainId()));
        self::assertFalse($nilId->equals(''));
        self::assertFalse($nilId->equals(new \stdClass()));
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
