<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use MsgPhp\Domain\Infra\Doctrine\EntityReferenceLoader;
use PHPUnit\Framework\TestCase;

final class EntityReferenceLoaderTest extends TestCase
{
    use EntityManagerTrait;

    private $createSchema = false;

    public function testInvoke(): void
    {
        $loader = new EntityReferenceLoader(self::$em, ['alias' => Entities\TestEntity::class]);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertInstanceOf(Entities\TestEntity::class, $entity = $loader('alias', $id));
        $this->assertSame($id, $entity->getId());
        $this->assertInstanceOf(Entities\TestCompositeEntity::class, $entity = $loader(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => 'b']));
        $this->assertSame($id, $entity->idA);
        $this->assertSame('b', $entity->idB);
        $this->assertInstanceOf(Entities\TestPrimitiveEntity::class, $entity = $loader(Entities\TestPrimitiveEntity::class, ['id' => $id]));
        $this->assertSame($id, $entity->id);
    }

    public function testInvokeWithEmptyIdentifier(): void
    {
        $loader = new EntityReferenceLoader(self::$em);
        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertNull($loader(\stdClass::class, null));
        $this->assertNull($loader(\stdClass::class, []));
        $this->assertNull($loader(Entities\TestEntity::class, $emptyId));
        $this->assertNull($loader(Entities\TestPrimitiveEntity::class, ['id' => $emptyId]));
        $this->assertNull($loader(Entities\TestCompositeEntity::class, ['idA' => $id, 'idB' => $emptyId]));
        $this->assertNull($loader(Entities\TestDerivedCompositeEntity::class, ['entity' => Entities\TestPrimitiveEntity::create(['id' => $emptyId]), 'id' => 0]));
    }

    public function testInvokeWithInvalidIdentifier(): void
    {
        $loader = new EntityReferenceLoader(self::$em);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertNull($loader(Entities\TestPrimitiveEntity::class, ['id' => $id, 'foo' => 'bar']));
    }

    public function testInvokeWithInvalidClass(): void
    {
        $loader = new EntityReferenceLoader(self::$em);

        $this->expectException(\ReflectionException::class);

        $loader('foo', 1);
    }
}
