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

    public function testGetIdentity(): void
    {
        $mapping = static::createMapping();
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(false);
        $entity = Entities\TestPrimitiveEntity::create(['id' => $id]);

        self::assertSame($identity = ['idA' => $id, 'idB' => 'foo'], $mapping->getIdentity(Entities\TestCompositeEntity::create($identity)));
        self::assertSame($identity = ['entity' => $entity, 'id' => 0], $mapping->getIdentity(Entities\TestDerivedCompositeEntity::create($identity)));
        self::assertSame($identity = ['entity' => $entity], $mapping->getIdentity(Entities\TestDerivedEntity::create($identity)));
        self::assertSame($identity = ['id' => $id], $mapping->getIdentity(Entities\TestEntity::create($identity + ['strField' => 'foo'])));
        self::assertSame($identity = ['id' => $id], $mapping->getIdentity(Entities\TestPrimitiveEntity::create($identity)));
    }

    public function testGetIdentityWithEmptyIdentifier(): void
    {
        $mapping = static::createMapping();
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects(self::any())
            ->method('isEmpty')
            ->willReturn(true);
        $entity = Entities\TestPrimitiveEntity::create(['id' => $id]);

        self::assertSame(['idB' => 'foo'], $mapping->getIdentity(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'foo'])));
        self::assertSame(['id' => 0], $mapping->getIdentity(Entities\TestDerivedCompositeEntity::create(['entity' => $entity, 'id' => 0])));
        self::assertSame([], $mapping->getIdentity(Entities\TestDerivedEntity::create(['entity' => $entity])));
        self::assertSame([], $mapping->getIdentity(Entities\TestEntity::create(['id' => $id, 'strField' => 'foo'])));
        self::assertSame([], $mapping->getIdentity(Entities\TestPrimitiveEntity::create(['id' => $id])));
    }

    public function testGetIdentityWithIncompleteIdentifier(): void
    {
        $mapping = static::createMapping();

        self::assertSame($identity = ['idB' => 'foo'], $mapping->getIdentity(Entities\TestCompositeEntity::create($identity)));
        self::assertSame($identity = ['id' => 1], $mapping->getIdentity(Entities\TestDerivedCompositeEntity::create($identity)));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $mapping = static::createMapping();

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentity(new \stdClass());
    }

    abstract protected static function createMapping(): DomainIdentityMappingInterface;
}
