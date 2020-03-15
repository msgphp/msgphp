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
    public function testFromValue(): void
    {
        self::assertInstanceOf(TestAbstractDomainId::class, TestAbstractDomainId::fromValue(null));
        self::assertInstanceOf(TestSubAbstractDomainId::class, TestSubAbstractDomainId::fromValue(null));
        self::assertInstanceOf(TestAbstractDomainId::class, TestAbstractDomainId::fromValue($this->createMock(DomainId::class)));
        self::assertInstanceOf(TestSubAbstractDomainId::class, TestAbstractDomainId::fromValue(new TestSubAbstractDomainId()));
        self::assertInstanceOf(TestAbstractDomainId::class, TestAbstractDomainId::fromValue(new TestOtherAbstractDomainId()));
        self::assertSame($id = TestAbstractDomainId::fromValue(null), TestAbstractDomainId::fromValue($id));
        self::assertSame((array) new TestAbstractDomainId($id = $this->createMock(DomainId::class)), (array) TestAbstractDomainId::fromValue($id));
        self::assertSame((new TestAbstractDomainId())->isNil(), TestAbstractDomainId::fromValue(null)->isNil());
    }

    public function testFromInvalidValue(): void
    {
        $this->expectException(\LogicException::class);

        TestAbstractDomainId::fromValue(true);
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
