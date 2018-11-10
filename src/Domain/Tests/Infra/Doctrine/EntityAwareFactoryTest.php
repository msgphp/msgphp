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

    private $createSchema = true;

    public function testCreate(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(Entities\TestEntity::class, ['foo' => 'bar'])
            ->willReturn($obj = new \stdClass());
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0);
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->create(Entities\TestEntity::class, ['foo' => 'bar']));
    }

    public function testCreateWithDiscriminator(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(Entities\TestChildEntity::class, ['foo' => 'bar', 'discriminator' => 'child'])
            ->willReturn($obj = new \stdClass());
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0);
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->create(Entities\TestParentEntity::class, ['foo' => 'bar', 'discriminator' => 'child']));
    }

    public function testCreateWithObject(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(\stdClass::class)
            ->willReturn($obj = new \stdClass());
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0);

        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->create(\stdClass::class));
    }

    public function testReference(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0);
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestEntity::class, $id = $this->createMock(DomainIdInterface::class)));
        self::assertInstanceOf(Entities\TestEntity::class, $ref);
        self::assertSame($id, $ref->getId());
    }

    public function testReferenceWithDiscriminator(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0);
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestParentEntity::class, ['id' => 'foo', 'discriminator' => 'child']));
        self::assertInstanceOf(Entities\TestChildEntity::class, $ref);
        self::assertSame('foo', $ref->id);
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
        $innerFactory->expects(self::once())
            ->method('identify')
            ->with('foo', 1)
            ->willReturn($obj = $this->createMock(DomainIdInterface::class));
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->identify('foo', 1));
    }

    public function testNextIdentifier(): void
    {
        $innerFactory = $this->createMock(EntityAwareFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('nextIdentifier')
            ->with('foo')
            ->willReturn($obj = $this->createMock(DomainIdInterface::class));
        $factory = new EntityAwareFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->nextIdentifier('foo'));
    }
}
