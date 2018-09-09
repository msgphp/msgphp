<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\{DomainIdInterface, DomainIdentityHelper, DomainIdentityMappingInterface};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainIdentityHelperTest extends TestCase
{
    private $mapping;

    protected function setUp(): void
    {
        $this->mapping = $this->createMock(DomainIdentityMappingInterface::class);
        $this->mapping->expects(self::any())
            ->method('getIdentifierFieldNames')
            ->willReturnCallback(function ($class): array {
                if (is_subclass_of($class, Entities\BaseTestEntity::class)) {
                    return $class::getIdFields();
                }

                throw InvalidClassException::create($class);
            });
        $this->mapping->expects(self::any())
            ->method('getIdentity')
            ->willReturnCallback(function ($object): array {
                if ($object instanceof Entities\BaseTestEntity) {
                    return Entities\BaseTestEntity::getPrimaryIds($object);
                }

                throw InvalidClassException::create(\get_class($object));
            });
    }

    public function testIsIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        self::assertTrue($helper->isIdentifier($this->createMock(DomainIdInterface::class)));
        self::assertTrue($helper->isIdentifier(Entities\TestEntity::create()));
        self::assertTrue($helper->isIdentifier(Entities\TestCompositeEntity::create()));
        self::assertFalse($helper->isIdentifier(new \stdClass()));
        self::assertFalse($helper->isIdentifier(null));
        self::assertFalse($helper->isIdentifier([]));
        self::assertFalse($helper->isIdentifier(1));
        self::assertFalse($helper->isIdentifier('foo'));
    }

    public function testIsEmptyIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false);

        self::assertTrue($helper->isEmptyIdentifier(null));
        self::assertTrue($helper->isEmptyIdentifier($emptyId));
        self::assertTrue($helper->isEmptyIdentifier(Entities\TestEntity::create()));
        self::assertTrue($helper->isEmptyIdentifier(Entities\TestEntity::create(['id' => $emptyId, 'strField' => 'foo'])));
        self::assertTrue($helper->isEmptyIdentifier(Entities\TestCompositeEntity::create(['idB' => 'foo'])));
        self::assertFalse($helper->isEmptyIdentifier($id));
        self::assertFalse($helper->isEmptyIdentifier(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        self::assertFalse($helper->isEmptyIdentifier([]));
        self::assertFalse($helper->isEmptyIdentifier(1));
        self::assertFalse($helper->isEmptyIdentifier('foo'));
        self::assertFalse($helper->isEmptyIdentifier(new \stdClass()));
    }

    public function testNormalizeIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false);
        $id->expects(self::any())
            ->method('toString')
            ->willReturn('id');

        self::assertNull($helper->normalizeIdentifier(null));
        self::assertNull($helper->normalizeIdentifier($emptyId));
        self::assertNull($helper->normalizeIdentifier(Entities\TestEntity::create()));
        self::assertNull($helper->normalizeIdentifier($entity = Entities\TestEntity::create(['id' => $emptyId, 'strField' => 'foo'])));
        self::assertNull($helper->normalizeIdentifier(Entities\TestDerivedEntity::create(['entity' => $entity])));
        self::assertSame('id', $helper->normalizeIdentifier($id));
        self::assertSame('id', $helper->normalizeIdentifier(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        self::assertSame(['idA' => 'id', 'idB' => null], $helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id])));
        self::assertSame(['idA' => 'id', 'idB' => 'id-b'], $helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'id-b'])));
        self::assertSame(['entity' => null, 'id' => 0], $helper->normalizeIdentifier(Entities\TestDerivedCompositeEntity::create(['entity' => Entities\TestPrimitiveEntity::create([]), 'id' => 0])));
        self::assertSame([], $helper->normalizeIdentifier([]));
        self::assertSame(['id' => 1], $helper->normalizeIdentifier(['id' => 1]));
        self::assertSame(1, $helper->normalizeIdentifier(1));
        self::assertSame('foo', $helper->normalizeIdentifier('foo'));
        self::assertSame($object = new \stdClass(), $helper->normalizeIdentifier($object));
    }

    public function testGetIdentifiers(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        self::assertSame([$id = $this->createMock(DomainIdInterface::class)], $helper->getIdentifiers($entity = Entities\TestEntity::create(['id' => $id])));
        self::assertSame([$entity], $helper->getIdentifiers(Entities\TestDerivedEntity::create(['entity' => $entity])));
        self::assertSame([null, 'bar'], $helper->getIdentifiers(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        self::assertSame([null], $helper->getIdentifiers(Entities\TestPrimitiveEntity::create()));
    }

    public function testGetIdentifiersWithInvalidEntity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentifiers(new \stdClass());
    }

    public function testGetIdentifierFieldNames(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        self::assertSame(['id'], $helper->getIdentifierFieldNames(Entities\TestPrimitiveEntity::class));
        self::assertSame(['idA', 'idB'], $helper->getIdentifierFieldNames(Entities\TestCompositeEntity::class));
    }

    public function testGetIdentifierFieldNamesWithInvalidEntity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentifierFieldNames(\stdClass::class);
    }

    public function testIsIdentity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false);

        self::assertTrue($helper->isIdentity(Entities\TestEntity::class, ['id' => $id]));
        self::assertTrue($helper->isIdentity(Entities\TestEntity::class, $id));
        self::assertTrue($helper->isIdentity(Entities\TestEntity::class, 'foo'));
        self::assertTrue($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b']));
        self::assertTrue($helper->isIdentity(Entities\TestDerivedEntity::class, ['entity' => Entities\TestEntity::create(['id' => $id])]));
        self::assertFalse($helper->isIdentity(Entities\TestEntity::class, null));
        self::assertFalse($helper->isIdentity(Entities\TestEntity::class, []));
        self::assertFalse($helper->isIdentity(Entities\TestEntity::class, ['id' => $emptyId]));
        self::assertFalse($helper->isIdentity(Entities\TestEntity::class, $emptyId));
        self::assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id]));
        self::assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => null]));
        self::assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $emptyId, 'idB' => 'foo']));
        self::assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b', 'foo' => 'bar']));
        self::assertFalse($helper->isIdentity(Entities\TestDerivedEntity::class, ['entity' => Entities\TestEntity::create(['id' => $emptyId])]));
    }

    public function testIsIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->isIdentity(\stdClass::class, ['id' => 1]);
    }

    public function testToIdentity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        self::assertSame([], $helper->toIdentity(Entities\TestEntity::class, []));
        self::assertSame([null], $helper->toIdentity(Entities\TestEntity::class, [null]));
        self::assertSame([1], $helper->toIdentity(Entities\TestEntity::class, [1]));
        self::assertSame(['foo' => 'bar', 'bar' => null], $helper->toIdentity(Entities\TestEntity::class, ['foo' => 'bar', 'bar' => null]));
        self::assertSame(['id' => null], $helper->toIdentity(Entities\TestEntity::class, null));
        self::assertSame(['id' => 'foo'], $helper->toIdentity(Entities\TestEntity::class, 'foo'));
        self::assertSame(['idA' => 'foo'], $helper->toIdentity(Entities\TestCompositeEntity::class, 'foo'));
    }

    public function testToIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->toIdentity(\stdClass::class, 1);
    }

    public function testGetIdentity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        self::assertSame(['id' => $id = $this->createMock(DomainIdInterface::class)], $helper->getIdentity(Entities\TestEntity::create(['id' => $id])));
        self::assertSame(['idA' => null, 'idB' => 'bar'], $helper->getIdentity(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        self::assertSame(['id' => null], $helper->getIdentity(Entities\TestPrimitiveEntity::create()));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentity(new \stdClass());
    }
}
