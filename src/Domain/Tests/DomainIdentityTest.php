<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\{DomainIdInterface, DomainIdentity, DomainIdentityMappingInterface};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainIdentityTest extends TestCase
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

                if (\stdClass::class === $class) {
                    return [];
                }

                throw InvalidClassException::create($class);
            });
        $this->mapping->expects($this->any())
            ->method('getIdentity')
            ->willReturnCallback(function ($object): array {
                if ($object instanceof Entities\BaseTestEntity) {
                    return array_filter(array_combine($object::getIdFields(), Entities\BaseTestEntity::getPrimaryIds($object)), function ($value): bool {
                        return null !== $value;
                    });
                }

                if ($object instanceof \stdClass) {
                    return [];
                }

                throw InvalidClassException::create(get_class($object));
            });
    }

    public function testIsIdentifier(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->assertTrue($identity->isIdentifier($this->createMock(DomainIdInterface::class)));
        $this->assertTrue($identity->isIdentifier(Entities\TestEntity::create()));
        $this->assertTrue($identity->isIdentifier(Entities\TestCompositeEntity::create()));
        $this->assertFalse($identity->isIdentifier(new \stdClass()));
        $this->assertFalse($identity->isIdentifier(new class() {
        }));
    }

    public function testIsEmptyIdentifier(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertTrue($identity->isEmptyIdentifier(null));
        $this->assertTrue($identity->isEmptyIdentifier($emptyId));
        $this->assertTrue($identity->isEmptyIdentifier(Entities\TestPrimitiveEntity::create()));
        $this->assertTrue($identity->isEmptyIdentifier(new \stdClass()));
        $this->assertFalse($identity->isEmptyIdentifier(Entities\TestCompositeEntity::create(['idB' => 'foo'])));
        $this->assertFalse($identity->isEmptyIdentifier($id));
        $this->assertFalse($identity->isEmptyIdentifier(1));
        $this->assertFalse($identity->isEmptyIdentifier('foo'));
        $this->assertFalse($identity->isEmptyIdentifier(new class() {
        }));
    }

    public function testNormalizeIdentifier(): void
    {
        $identity = new DomainIdentity($this->mapping);

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

        $this->assertNull($identity->normalizeIdentifier(null));
        $this->assertNull($identity->normalizeIdentifier($emptyId));
        $this->assertNull($identity->normalizeIdentifier(Entities\TestPrimitiveEntity::create()));
        $this->assertNull($identity->normalizeIdentifier(new \stdClass()));
        $this->assertSame('id', $identity->normalizeIdentifier($id));
        $this->assertSame('id', $identity->normalizeIdentifier(Entities\TestPrimitiveEntity::create(['id' => 'id'])));
        $this->assertSame(['id'], $identity->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id])));
        $this->assertSame(['id', 'id-b'], $identity->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'id-b'])));
        $this->assertSame(1, $identity->normalizeIdentifier(1));
        $this->assertSame('foo', $identity->normalizeIdentifier('foo'));
        $this->assertSame($object = new class() {
        }, $identity->normalizeIdentifier($object));
    }

    public function testGetIdentifiers(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->assertSame(['foo'], $identity->getIdentifiers(Entities\TestPrimitiveEntity::create(['id' => 'foo'])));
        $this->assertSame(['bar'], $identity->getIdentifiers(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        $this->assertSame([], $identity->getIdentifiers(Entities\TestPrimitiveEntity::create()));
        $this->assertSame([], $identity->getIdentifiers(new \stdClass()));
    }

    public function testGetIdentifiersWithInvalidEntity(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->expectException(InvalidClassException::class);

        $identity->getIdentifiers(new class() {
        });
    }

    public function testIsIdentity(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->assertTrue($identity->isIdentity(Entities\TestCompositeEntity::class, ['idA' => 'a', 'idB' => 'b']));
        $this->assertFalse($identity->isIdentity(Entities\TestCompositeEntity::class, ['idA' => 'a', 'idB' => null]));
        $this->assertTrue($identity->isIdentity(Entities\TestPrimitiveEntity::class, ['id' => 1]));
        $this->assertFalse($identity->isIdentity(Entities\TestPrimitiveEntity::class, []));
        $this->assertFalse($identity->isIdentity(Entities\TestPrimitiveEntity::class, ['foo' => 'bar']));
        $this->assertFalse($identity->isIdentity(Entities\TestPrimitiveEntity::class, ['id' => 1, 'foo' => 'bar']));
    }

    public function testIsIdentityWithInvalidClass(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->expectException(InvalidClassException::class);

        $identity->isIdentity('foo', ['id' => 1]);
    }

    public function testToIdentity(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->assertSame(['idA' => 'a', 'idB' => 'b'], $identity->toIdentity(Entities\TestCompositeEntity::class, 'a', 'b'));
        $this->assertNull($identity->toIdentity(Entities\TestCompositeEntity::class, 'a', null));
        $this->assertSame(['id' => 1], $identity->toIdentity(Entities\TestPrimitiveEntity::class, 1));
        $this->assertNull($identity->toIdentity(Entities\TestPrimitiveEntity::class, null));
        $this->assertNull($identity->toIdentity(Entities\TestPrimitiveEntity::class, 1, 'foo'));
    }

    public function testToIdentityWithInvalidClass(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->expectException(InvalidClassException::class);

        $identity->toIdentity('foo', 1);
    }

    public function testGetIdentity(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->assertSame(['id' => 'foo'], $identity->getIdentity(Entities\TestPrimitiveEntity::create(['id' => 'foo'])));
        $this->assertSame(['idB' => 'bar'], $identity->getIdentity(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        $this->assertSame([], $identity->getIdentity(Entities\TestPrimitiveEntity::create()));
        $this->assertSame([], $identity->getIdentity(new \stdClass()));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $identity = new DomainIdentity($this->mapping);

        $this->expectException(InvalidClassException::class);

        $identity->getIdentity(new class() {
        });
    }
}
