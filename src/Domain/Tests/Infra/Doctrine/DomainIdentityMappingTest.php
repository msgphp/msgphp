<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainIdentityMappingTest extends TestCase
{
    use EntityManagerTrait;

    public function testGetIdentifierFieldNames(): void
    {
        $mapping = new DomainIdentityMapping(self::$em);

        $this->assertSame(['id'], $mapping->getIdentifierFieldNames(Entities\TestEntity::class));
        $this->assertSame(['idA', 'idB'], $mapping->getIdentifierFieldNames(Entities\TestCompositeEntity::class));
    }

    public function testGetIdentifierFieldNamesWithInvalidClass(): void
    {
        $mapping = new DomainIdentityMapping(self::$em);

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentifierFieldNames(\stdClass::class);
    }

    public function testGetIdentity(): void
    {
        $mapping = new DomainIdentityMapping(self::$em);
        $object1 = Entities\TestPrimitiveEntity::create($id1 = ['id' => $this->createMock(DomainIdInterface::class)]);
        $object2 = Entities\TestDerivedCompositeEntity::create($id2 = ['entity' => $object1, 'id' => 'foo']);

        $this->assertSame($id1, $mapping->getIdentity($object1));
        $this->assertSame($id2, $mapping->getIdentity($object2));
        $this->assertNull($mapping->getIdentity(Entities\TestEntity::create(['strField' => 'foo'])));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $mapping = new DomainIdentityMapping(self::$em);

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentity(new \stdClass());
    }
}
