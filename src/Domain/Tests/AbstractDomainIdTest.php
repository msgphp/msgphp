<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\AbstractDomainId;
use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\GenericDomainId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AbstractDomainIdTest extends TestCase
{
    public function testFromInt(): void
    {
        self::assertTrue(TestAbstractDomainId::fromInt(null)->isNil());
        self::assertFalse(TestAbstractDomainId::fromInt(-123)->isNil());
        self::assertSame('-123', TestAbstractDomainId::fromInt(-123)->toString());
        self::assertTrue(TestAbstractDomainId::fromInt(0)->isNil());
        self::assertSame('123', TestAbstractDomainId::fromInt(123)->toString());
        self::assertFalse(TestAbstractDomainId::fromInt(123)->isNil());
    }

    public function testIsNil(): void
    {
        $id = $this->createMock(DomainId::class);
        $id->expects(self::once())
            ->method('isNil')
            ->with()
            ->willReturn(true)
        ;

        self::assertTrue((new TestAbstractDomainId($id))->isNil());
    }

    public function testEquals(): void
    {
        $id = $this->createMock(DomainId::class);
        $id->expects(self::exactly(2))
            ->method('equals')
            ->with($id)
            ->willReturn(true)
        ;

        self::assertFalse((new TestAbstractDomainId($id))->equals(''));
        self::assertFalse((new TestAbstractDomainId($id))->equals($id));
        self::assertTrue((new TestAbstractDomainId($id))->equals(new TestAbstractDomainId($id)));
        self::assertTrue((new TestAbstractDomainId($id))->equals(new TestSubAbstractDomainId($id)));
        self::assertFalse((new TestAbstractDomainId($id))->equals(new TestOtherAbstractDomainId($id)));
    }

    public function testToString(): void
    {
        $id = $this->createMock(DomainId::class);
        $id->expects(self::once())
            ->method('toString')
            ->with()
            ->willReturn('foo')
        ;

        self::assertSame('foo', (new TestAbstractDomainId($id))->toString());
    }

    public function testSerialize(): void
    {
        self::assertSame('foo', (string) unserialize(serialize(new TestAbstractDomainId(new GenericDomainId('foo')))));
    }
}

class TestAbstractDomainId extends AbstractDomainId
{
}

class TestSubAbstractDomainId extends TestAbstractDomainId
{
}

class TestOtherAbstractDomainId extends AbstractDomainId
{
}
