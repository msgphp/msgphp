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
        $this->mapping->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->willReturnCallback(function ($class): array {
                if (is_subclass_of($class, Entities\BaseTestEntity::class)) {
                    return $class::getIdFields();
                }

                throw InvalidClassException::create($class);
            });
        $this->mapping->expects($this->any())
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

        $this->assertTrue($helper->isIdentifier($this->createMock(DomainIdInterface::class)));
        $this->assertTrue($helper->isIdentifier(Entities\TestEntity::create()));
        $this->assertTrue($helper->isIdentifier(Entities\TestCompositeEntity::create()));
        $this->assertFalse($helper->isIdentifier(new \stdClass()));
        $this->assertFalse($helper->isIdentifier(null));
        $this->assertFalse($helper->isIdentifier([]));
        $this->assertFalse($helper->isIdentifier(1));
        $this->assertFalse($helper->isIdentifier('foo'));
    }

    public function testIsEmptyIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertTrue($helper->isEmptyIdentifier(null));
        $this->assertTrue($helper->isEmptyIdentifier($emptyId));
        $this->assertTrue($helper->isEmptyIdentifier(Entities\TestEntity::create()));
        $this->assertTrue($helper->isEmptyIdentifier(Entities\TestEntity::create(['id' => $emptyId, 'strField' => 'foo'])));
        $this->assertTrue($helper->isEmptyIdentifier(Entities\TestCompositeEntity::create(['idB' => 'foo'])));
        $this->assertFalse($helper->isEmptyIdentifier($id));
        $this->assertFalse($helper->isEmptyIdentifier(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        $this->assertFalse($helper->isEmptyIdentifier([]));
        $this->assertFalse($helper->isEmptyIdentifier(1));
        $this->assertFalse($helper->isEmptyIdentifier('foo'));
        $this->assertFalse($helper->isEmptyIdentifier(new \stdClass()));
    }

    public function testNormalizeIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);
        $id->expects($this->any())
            ->method('toString')
            ->willReturn('id');

        $this->assertNull($helper->normalizeIdentifier(null));
        $this->assertNull($helper->normalizeIdentifier($emptyId));
        $this->assertNull($helper->normalizeIdentifier(Entities\TestEntity::create()));
        $this->assertNull($helper->normalizeIdentifier($entity = Entities\TestEntity::create(['id' => $emptyId, 'strField' => 'foo'])));
        $this->assertNull($helper->normalizeIdentifier(Entities\TestDerivedEntity::create(['entity' => $entity])));
        $this->assertSame('id', $helper->normalizeIdentifier($id));
        $this->assertSame('id', $helper->normalizeIdentifier(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        $this->assertSame(['idA' => 'id', 'idB' => null], $helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id])));
        $this->assertSame(['idA' => 'id', 'idB' => 'id-b'], $helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'id-b'])));
        $this->assertSame(['entity' => null, 'id' => 0], $helper->normalizeIdentifier(Entities\TestDerivedCompositeEntity::create(['entity' => Entities\TestPrimitiveEntity::create([]), 'id' => 0])));
        $this->assertSame([], $helper->normalizeIdentifier([]));
        $this->assertSame(['id' => 1], $helper->normalizeIdentifier(['id' => 1]));
        $this->assertSame(1, $helper->normalizeIdentifier(1));
        $this->assertSame('foo', $helper->normalizeIdentifier('foo'));
        $this->assertSame($object = new \stdClass(), $helper->normalizeIdentifier($object));
    }

    public function testGetIdentifiers(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertSame([$id = $this->createMock(DomainIdInterface::class)], $helper->getIdentifiers($entity = Entities\TestEntity::create(['id' => $id])));
        $this->assertSame([$entity], $helper->getIdentifiers(Entities\TestDerivedEntity::create(['entity' => $entity])));
        $this->assertSame([null, 'bar'], $helper->getIdentifiers(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        $this->assertSame([null], $helper->getIdentifiers(Entities\TestPrimitiveEntity::create()));
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

        $this->assertSame(['id'], $helper->getIdentifierFieldNames(Entities\TestPrimitiveEntity::class));
        $this->assertSame(['idA', 'idB'], $helper->getIdentifierFieldNames(Entities\TestCompositeEntity::class));
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
        $emptyId->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertTrue($helper->isIdentity(Entities\TestEntity::class, ['id' => $id]));
        $this->assertTrue($helper->isIdentity(Entities\TestEntity::class, $id));
        $this->assertTrue($helper->isIdentity(Entities\TestEntity::class, 'foo'));
        $this->assertTrue($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b']));
        $this->assertTrue($helper->isIdentity(Entities\TestDerivedEntity::class, ['entity' => Entities\TestEntity::create(['id' => $id])]));
        $this->assertFalse($helper->isIdentity(Entities\TestEntity::class, null));
        $this->assertFalse($helper->isIdentity(Entities\TestEntity::class, []));
        $this->assertFalse($helper->isIdentity(Entities\TestEntity::class, ['id' => $emptyId]));
        $this->assertFalse($helper->isIdentity(Entities\TestEntity::class, $emptyId));
        $this->assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id]));
        $this->assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => null]));
        $this->assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $emptyId, 'idB' => 'foo']));
        $this->assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b', 'foo' => 'bar']));
        $this->assertFalse($helper->isIdentity(Entities\TestDerivedEntity::class, ['entity' => Entities\TestEntity::create(['id' => $emptyId])]));
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

        $this->assertSame([], $helper->toIdentity(Entities\TestEntity::class, []));
        $this->assertSame([null], $helper->toIdentity(Entities\TestEntity::class, [null]));
        $this->assertSame([1], $helper->toIdentity(Entities\TestEntity::class, [1]));
        $this->assertSame(['foo' => 'bar', 'bar' => null], $helper->toIdentity(Entities\TestEntity::class, ['foo' => 'bar', 'bar' => null]));
        $this->assertSame(['id' => null], $helper->toIdentity(Entities\TestEntity::class, null));
        $this->assertSame(['id' => 'foo'], $helper->toIdentity(Entities\TestEntity::class, 'foo'));
        $this->assertSame(['idA' => 'foo'], $helper->toIdentity(Entities\TestCompositeEntity::class, 'foo'));
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

        $this->assertSame(['id' => $id = $this->createMock(DomainIdInterface::class)], $helper->getIdentity(Entities\TestEntity::create(['id' => $id])));
        $this->assertSame(['idA' => null, 'idB' => 'bar'], $helper->getIdentity(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        $this->assertSame(['id' => null], $helper->getIdentity(Entities\TestPrimitiveEntity::create()));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentity(new \stdClass());
    }
}
