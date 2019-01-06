<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainIdentityMappingInterface;
use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

abstract class AbstractDomainIdentityMappingTest extends TestCase
{
    public function testGetIdentifierFieldNames(): void
    {
        $mapping = static::createMapping();

        self::assertSame(['idA', 'idB'], $mapping->getIdentifierFieldNames(Entities\TestCompositeEntity::class));
        self::assertSame(['entity', 'id'], $mapping->getIdentifierFieldNames(Entities\TestDerivedCompositeEntity::class));
        self::assertSame(['entity'], $mapping->getIdentifierFieldNames(Entities\TestDerivedEntity::class));
        self::assertSame(['id'], $mapping->getIdentifierFieldNames(Entities\TestEntity::class));
        self::assertSame(['id'], $mapping->getIdentifierFieldNames(Entities\TestPrimitiveEntity::class));
    }

    public function testGetIdentifierFieldNamesWithInvalidClass(): void
    {
        $mapping = static::createMapping();

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentifierFieldNames(\stdClass::class);
    }

    public function testGetIdentifiers(): void
    {
        $mapping = static::createMapping();
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false)
        ;
        $entity = Entities\TestPrimitiveEntity::create(['id' => $id]);

        self::assertSame($identity = ['idA' => $id, 'idB' => 'foo'], $mapping->getIdentifiers(Entities\TestCompositeEntity::create($identity)));
        self::assertSame($identity = ['entity' => $entity, 'id' => 0], $mapping->getIdentifiers(Entities\TestDerivedCompositeEntity::create($identity)));
        self::assertSame($identity = ['entity' => $entity], $mapping->getIdentifiers(Entities\TestDerivedEntity::create($identity)));
        self::assertSame($identity = ['id' => $id], $mapping->getIdentifiers(Entities\TestEntity::create($identity + ['strField' => 'foo'])));
        self::assertSame($identity = ['id' => $id], $mapping->getIdentifiers(Entities\TestPrimitiveEntity::create($identity)));
    }

    public function testGetIdentifiersWithEmptyIdentifier(): void
    {
        $mapping = static::createMapping();
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true)
        ;
        $entity = Entities\TestPrimitiveEntity::create(['id' => $id]);

        self::assertSame(['idB' => 'foo'], $mapping->getIdentifiers(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'foo'])));
        self::assertSame(['id' => 0], $mapping->getIdentifiers(Entities\TestDerivedCompositeEntity::create(['entity' => $entity, 'id' => 0])));
        self::assertSame([], $mapping->getIdentifiers(Entities\TestDerivedEntity::create(['entity' => $entity])));
        self::assertSame([], $mapping->getIdentifiers(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        self::assertSame([], $mapping->getIdentifiers(Entities\TestPrimitiveEntity::create(['id' => $id])));
    }

    public function testGetIdentifiersWithIncompleteIdentifier(): void
    {
        $mapping = static::createMapping();

        self::assertSame($identity = ['idB' => 'foo'], $mapping->getIdentifiers(Entities\TestCompositeEntity::create($identity)));
        self::assertSame($identity = ['id' => 1], $mapping->getIdentifiers(Entities\TestDerivedCompositeEntity::create($identity)));
    }

    public function testGetIdentifiersWithInvalidClass(): void
    {
        $mapping = static::createMapping();

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentifiers(new \stdClass());
    }

    abstract protected static function createMapping(): DomainIdentityMappingInterface;
}
