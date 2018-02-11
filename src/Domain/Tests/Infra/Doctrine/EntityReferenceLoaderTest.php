<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\ORM\ORMInvalidArgumentException;
use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use MsgPhp\Domain\Infra\Doctrine\EntityReferenceLoader;
use PHPUnit\Framework\TestCase;

final class EntityReferenceLoaderTest extends TestCase
{
    use EntityManagerTrait;

    public function testInvoke(): void
    {
        $loader = new EntityReferenceLoader(self::$em, ['alias' => Entities\TestEntity::class]);

        $this->assertNull($loader(\stdClass::class, []));
        $this->assertInstanceOf(Entities\TestEntity::class, $entity = $loader('alias', [$id = $this->createMock(DomainIdInterface::class)]));
        $this->assertSame($id, $entity->getId());
        $this->assertInstanceOf(Entities\TestCompositeEntity::class, $entity = $loader(Entities\TestCompositeEntity::class, [$id = $this->createMock(DomainIdInterface::class), 'b']));
        $this->assertSame($id, $entity->idA);
        $this->assertSame('b', $entity->idB);
    }

    public function testInvokeWithInvalidClass(): void
    {
        $loader = new EntityReferenceLoader(self::$em);

        $this->expectException(\ReflectionException::class);

        $loader('foo', [1]);
    }

    public function testInvokeWithInvalidIdentity(): void
    {
        $loader = new EntityReferenceLoader(self::$em);

        $this->expectException(ORMInvalidArgumentException::class);

        $loader(Entities\TestPrimitiveEntity::class, [1, 2]);
    }
}
