<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use Doctrine\ORM\Proxy\Proxy;
use MsgPhp\Domain\Factory\DomainObjectFactory as BaseDomainObjectFactory;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainObjectFactory;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DomainObjectFactoryTest extends TestCase
{
    use EntityManagerTestTrait;

    public function testCreate(): void
    {
        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(Entities\TestEntity::class, ['foo' => 'bar'])
            ->willReturn($obj = Entities\TestEntity::create())
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
        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
        $innerFactory->expects(self::once())
            ->method('create')
            ->with(Entities\TestChildEntity::class, ['foo' => 'bar', 'discriminator' => 'child'])
            ->willReturn($obj = Entities\TestChildEntity::create())
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
        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
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

    public function testGetClass(): void
    {
        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertSame(Entities\TestParentEntity::class, $factory->getClass(Entities\TestParentEntity::class));
    }

    public function testGetClassWithDiscriminator(): void
    {
        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertSame(Entities\TestChildEntity::class, $factory->getClass(Entities\TestParentEntity::class, ['discriminator' => 'child']));
    }

    public function testReference(): void
    {
        self::$em->persist($entity = Entities\TestEntity::create(['intField' => 123, 'boolField' => false]));
        self::$em->flush();
        self::$em->clear();

        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
        $innerFactory->expects(self::once())
            ->method('getClass')
            ->willReturnArgument(0)
        ;
        $factory = new DomainObjectFactory($innerFactory, self::$em);

        self::assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestEntity::class, ['id' => 1]));
        self::assertSame('1', $ref->getId()->toString());
        self::assertSame(123, $ref->intField);
        self::assertFalse($ref->boolField);
    }

    public function testReferenceWithDiscriminator(): void
    {
        self::$em->persist(Entities\TestChildEntity::create(['id' => 'child']));
        self::$em->flush();
        self::$em->clear();

        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
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
        $innerFactory = $this->createMock(BaseDomainObjectFactory::class);
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
