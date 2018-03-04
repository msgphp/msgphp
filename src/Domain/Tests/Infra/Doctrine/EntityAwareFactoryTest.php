<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\ORM\Proxy\Proxy;
use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Infra\Doctrine\EntityAwareFactory;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class EntityAwareFactoryTest extends TestCase
{
    use EntityManagerTrait;

    private $createSchema = false;

    public function testCreate(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects($this->once())
            ->method('create')
            ->with(Entities\TestEntity::class, ['foo' => 'bar'])
            ->willReturn($obj = new \stdClass());
        $factory = new EntityAwareFactory($innerFactory, self::$em, ['alias' => Entities\TestEntity::class]);

        $this->assertSame($obj, $factory->create('alias', ['foo' => 'bar']));
    }

    public function testCreateWithDiscriminator(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects($this->once())
            ->method('create')
            ->with(Entities\TestChildEntity::class, ['foo' => 'bar', 'discriminator' => 'child'])
            ->willReturn($obj = new \stdClass());
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        $this->assertSame($obj, $factory->create(Entities\TestParentEntity::class, ['foo' => 'bar', 'discriminator' => 'child']));
    }

    public function testCreateWithUnknownClass(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->create('foo');
    }

    public function testCreateWithUnknownEntity(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->create(\stdClass::class);
    }

    public function testReference(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em, ['alias' => Entities\TestEntity::class]);

        $this->assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestEntity::class, $id = $this->createMock(DomainIdInterface::class)));
        $this->assertInstanceOf(Entities\TestEntity::class, $ref);
        $this->assertSame($id, $ref->getId());
        $this->assertInstanceOf(Proxy::class, $ref = $factory->reference('alias', $id));
        $this->assertInstanceOf(Entities\TestEntity::class, $ref);
        $this->assertSame($id, $ref->getId());
    }

    public function testReferenceWithDiscriminator(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestParentEntity::class, ['id' => 'foo', 'discriminator' => 'child']));
        $this->assertInstanceOf(Entities\TestChildEntity::class, $ref);
        $this->assertSame('foo', $ref->id);
    }

    public function testReferenceWithUnknownClass(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->reference('foo', 1);
    }

    public function testReferenceWithUnknownEntity(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->reference(\stdClass::class, 1);
    }

    public function testIdentify(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects($this->once())
            ->method('identify')
            ->with(Entities\TestEntity::class, 1)
            ->willReturn($obj = $this->createMock(DomainIdInterface::class));
        $factory = new EntityAwareFactory($innerFactory, self::$em, ['alias' => Entities\TestEntity::class]);

        $this->assertSame($obj, $factory->identify('alias', 1));
    }

    public function testIdentifyWithUnknownClass(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->identify('foo', 1);
    }

    public function testIdentifyWithUnknownEntity(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->identify(\stdClass::class, 1);
    }

    public function testNextIdentifier(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects($this->once())
            ->method('nextIdentifier')
            ->with(Entities\TestEntity::class)
            ->willReturn($obj = $this->createMock(DomainIdInterface::class));
        $factory = new EntityAwareFactory($innerFactory, self::$em, ['alias' => Entities\TestEntity::class]);

        $this->assertSame($obj, $factory->nextIdentifier('alias'));
    }

    public function testNextIdentifierWithUnknownClass(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->nextIdentifier('foo');
    }

    public function testNextIdentifierUnknownEntity(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->expectException(InvalidClassException::class);

        $factory->nextIdentifier(\stdClass::class);
    }
}
