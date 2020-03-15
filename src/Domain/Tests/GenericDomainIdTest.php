<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\GenericDomainId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GenericDomainIdTest extends TestCase
{
    public function testFromValue(): void
    {
        self::assertInstanceOf(GenericDomainId::class, GenericDomainId::fromValue(null));
        self::assertSame((array) new GenericDomainId(), (array) GenericDomainId::fromValue(null));
        self::assertSame((array) new GenericDomainId(null), (array) GenericDomainId::fromValue(null));
        self::assertSame((array) new GenericDomainId(''), (array) GenericDomainId::fromValue(null));
        self::assertSame((array) new GenericDomainId('foo'), (array) GenericDomainId::fromValue('foo'));
        self::assertSame((array) new GenericDomainId('1'), (array) GenericDomainId::fromValue(1));
        self::assertSame((array) new GenericDomainId(' '), (array) GenericDomainId::fromValue(' '));
    }

    public function testFromInvalidValue(): void
    {
        $this->expectException(\LogicException::class);

        GenericDomainId::fromValue(true);
    }

    public function testIsNil(): void
    {
        self::assertTrue((new GenericDomainId())->isNil());
        self::assertTrue((new GenericDomainId(null))->isNil());
        self::assertTrue((new GenericDomainId(''))->isNil());
        self::assertFalse((new GenericDomainId(' '))->isNil());
        self::assertFalse((new GenericDomainId('foo'))->isNil());
    }

    public function testEquals(): void
    {
        $id = new GenericDomainId('foo');
        $nilId = new GenericDomainId();
        $otherId = $this->createMock(DomainId::class);
        $otherId->expects(self::once())
            ->method('toString')
            ->willReturn('foo')
        ;

        self::assertTrue($id->equals($id));
        self::assertTrue($id->equals(new GenericDomainId('foo')));
        self::assertFalse($id->equals(new GenericDomainId()));
        self::assertFalse($id->equals('foo'));
        self::assertFalse($id->equals(new \stdClass()));
        self::assertTrue($nilId->equals($nilId));
        self::assertTrue($nilId->equals(new GenericDomainId()));
        self::assertFalse($nilId->equals(''));
        self::assertFalse($nilId->equals(new \stdClass()));
        self::assertTrue($id->equals($otherId));
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
        yield [new GenericDomainId(), ''];
        yield [new GenericDomainId(null), ''];
        yield [new GenericDomainId('foo'), 'foo'];
        yield [new GenericDomainId(' '), ' '];
    }
}
