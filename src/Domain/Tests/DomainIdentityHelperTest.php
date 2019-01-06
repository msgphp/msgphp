<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\{DomainIdInterface, DomainIdentityHelper};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainIdentityHelperTest extends TestCase
{
    private $helper;

    protected function setUp(): void
    {
        $this->helper = new DomainIdentityHelper(new DomainIdentityMapping([
            Entities\TestEntity::class => Entities\TestEntity::getIdFields(),
            Entities\TestCompositeEntity::class => Entities\TestCompositeEntity::getIdFields(),
            Entities\TestDerivedEntity::class => Entities\TestDerivedEntity::getIdFields(),
            Entities\TestDerivedCompositeEntity::class => Entities\TestDerivedCompositeEntity::getIdFields(),
            Entities\TestPrimitiveEntity::class => Entities\TestPrimitiveEntity::getIdFields(),
        ]));
    }

    public function testIsIdentifier(): void
    {
        self::assertTrue($this->helper->isIdentifier($this->createMock(DomainIdInterface::class)));
        self::assertTrue($this->helper->isIdentifier(Entities\TestEntity::create()));
        self::assertTrue($this->helper->isIdentifier(Entities\TestCompositeEntity::create()));
        self::assertFalse($this->helper->isIdentifier(new \stdClass()));
        self::assertFalse($this->helper->isIdentifier(null));
        self::assertFalse($this->helper->isIdentifier([]));
        self::assertFalse($this->helper->isIdentifier(1));
        self::assertFalse($this->helper->isIdentifier('foo'));
    }

    public function testIsEmptyIdentifier(): void
    {
        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true)
        ;
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false)
        ;

        self::assertTrue($this->helper->isEmptyIdentifier(null));
        self::assertTrue($this->helper->isEmptyIdentifier($emptyId));
        self::assertTrue($this->helper->isEmptyIdentifier(Entities\TestEntity::create()));
        self::assertTrue($this->helper->isEmptyIdentifier(Entities\TestEntity::create(['id' => $emptyId, 'strField' => 'foo'])));
        self::assertTrue($this->helper->isEmptyIdentifier(Entities\TestCompositeEntity::create(['idB' => 'foo'])));
        self::assertFalse($this->helper->isEmptyIdentifier($id));
        self::assertFalse($this->helper->isEmptyIdentifier(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        self::assertFalse($this->helper->isEmptyIdentifier([]));
        self::assertFalse($this->helper->isEmptyIdentifier(1));
        self::assertFalse($this->helper->isEmptyIdentifier('foo'));
        self::assertFalse($this->helper->isEmptyIdentifier(new \stdClass()));
    }

    public function testNormalizeIdentifier(): void
    {
        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true)
        ;
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false)
        ;
        $id->expects(self::any())
            ->method('toString')
            ->willReturn('id')
        ;

        self::assertNull($this->helper->normalizeIdentifier(null));
        self::assertNull($this->helper->normalizeIdentifier($emptyId));
        self::assertNull($this->helper->normalizeIdentifier(Entities\TestEntity::create()));
        self::assertNull($this->helper->normalizeIdentifier($entity = Entities\TestEntity::create(['id' => $emptyId, 'strField' => 'foo'])));
        self::assertNull($this->helper->normalizeIdentifier(Entities\TestDerivedEntity::create(['entity' => $entity])));
        self::assertSame('id', $this->helper->normalizeIdentifier($id));
        self::assertSame('id', $this->helper->normalizeIdentifier(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        self::assertSame(['idA' => 'id'], $this->helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id])));
        self::assertSame(['idA' => 'id', 'idB' => 'id-b'], $this->helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'id-b'])));
        self::assertSame(['id' => 0], $this->helper->normalizeIdentifier(Entities\TestDerivedCompositeEntity::create(['entity' => Entities\TestPrimitiveEntity::create([]), 'id' => 0])));
        self::assertSame([], $this->helper->normalizeIdentifier([]));
        self::assertSame(['id' => 1], $this->helper->normalizeIdentifier(['id' => 1]));
        self::assertSame(1, $this->helper->normalizeIdentifier(1));
        self::assertSame('foo', $this->helper->normalizeIdentifier('foo'));
        self::assertSame($object = new \stdClass(), $this->helper->normalizeIdentifier($object));
    }

    public function testIsIdentity(): void
    {
        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true)
        ;
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false)
        ;

        self::assertTrue($this->helper->isIdentity(Entities\TestEntity::class, ['id' => $id]));
        self::assertTrue($this->helper->isIdentity(Entities\TestEntity::class, $id));
        self::assertTrue($this->helper->isIdentity(Entities\TestEntity::class, 'foo'));
        self::assertTrue($this->helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b']));
        self::assertTrue($this->helper->isIdentity(Entities\TestDerivedEntity::class, ['entity' => Entities\TestEntity::create(['id' => $id])]));
        self::assertFalse($this->helper->isIdentity(Entities\TestEntity::class, null));
        self::assertFalse($this->helper->isIdentity(Entities\TestEntity::class, []));
        self::assertFalse($this->helper->isIdentity(Entities\TestEntity::class, ['id' => $emptyId]));
        self::assertFalse($this->helper->isIdentity(Entities\TestEntity::class, $emptyId));
        self::assertFalse($this->helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id]));
        self::assertFalse($this->helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => null]));
        self::assertFalse($this->helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $emptyId, 'idB' => 'foo']));
        self::assertFalse($this->helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b', 'foo' => 'bar']));
        self::assertFalse($this->helper->isIdentity(Entities\TestDerivedEntity::class, ['entity' => Entities\TestEntity::create(['id' => $emptyId])]));
    }

    public function testIsIdentityWithInvalidClass(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->helper->isIdentity(\stdClass::class, ['id' => 1]);
    }

    public function testToIdentity(): void
    {
        self::assertSame([], $this->helper->toIdentity(Entities\TestEntity::class, []));
        self::assertSame([null], $this->helper->toIdentity(Entities\TestEntity::class, [null]));
        self::assertSame([1], $this->helper->toIdentity(Entities\TestEntity::class, [1]));
        self::assertSame(['foo' => 'bar', 'bar' => null], $this->helper->toIdentity(Entities\TestEntity::class, ['foo' => 'bar', 'bar' => null]));
        self::assertSame(['id' => null], $this->helper->toIdentity(Entities\TestEntity::class, null));
        self::assertSame(['id' => 'foo'], $this->helper->toIdentity(Entities\TestEntity::class, 'foo'));
        self::assertSame(['idA' => 'foo'], $this->helper->toIdentity(Entities\TestCompositeEntity::class, 'foo'));
    }

    public function testToIdentityWithInvalidClass(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->helper->toIdentity(\stdClass::class, 1);
    }
}
