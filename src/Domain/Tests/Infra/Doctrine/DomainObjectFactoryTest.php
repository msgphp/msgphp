<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\ORM\Proxy\Proxy;
use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainObjectFactory;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainObjectFactoryTest extends TestCase
{
    use EntityManagerTrait;

    private $createSchema = true;

    public function testCreate(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(Entities\TestEntity::class, ['foo' => 'bar'])
            ->willReturn($obj = new \stdClass())
        ;
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->create(Entities\TestEntity::class, ['foo' => 'bar']));
    }

    public function testCreateWithDiscriminator(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(Entities\TestChildEntity::class, ['foo' => 'bar', 'discriminator' => 'child'])
            ->willReturn($obj = new \stdClass())
        ;
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->create(Entities\TestParentEntity::class, ['foo' => 'bar', 'discriminator' => 'child']));
    }

    public function testCreateWithObject(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(\stdClass::class)
            ->willReturn($obj = new \stdClass())
        ;
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;

        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertSame($obj, $factory->create(\stdClass::class));
    }

    public function testReference(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestEntity::class, ['id' => $id = $this->createMock(DomainIdInterface::class)]));
        self::assertInstanceOf(Entities\TestEntity::class, $ref);
        self::assertSame($id, $ref->getId());
    }

    public function testReferenceWithDiscriminator(): void
    {
        self::$em->persist(Entities\TestChildEntity::create(['id' => 'child']));
        self::$em->flush();
        self::$em->clear();

        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $ref = (new DomainObjectFactory($innerFactory, self::$em))->reference(Entities\TestParentEntity::class, ['id' => 'child']);

        self::assertInstanceOf(Entities\TestChildEntity::class, $ref);
        self::assertSame('child', $ref->id);
    }

    public function testReferenceWithUnknownClass(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::once())
            ->method('reference')
            ->with(\stdClass::class, ['foo' => 'bar'])
            ->willReturn($ref = new \stdClass())
        ;
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;

        self::assertSame($ref, (new DomainObjectFactory($innerFactory, self::$em))->reference(\stdClass::class, ['foo' => 'bar']));
    }
}
