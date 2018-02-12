<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\{DomainIdInterface, DomainIdentityHelper, DomainIdentityMappingInterface};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use PHPUnit\Framework\TestCase;

final class DomainIdentityHelperTest extends TestCase
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
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertTrue($helper->isIdentifier($this->createMock(DomainIdInterface::class)));
        $this->assertTrue($helper->isIdentifier(Entities\TestEntity::create()));
        $this->assertTrue($helper->isIdentifier(Entities\TestCompositeEntity::create()));
        $this->assertFalse($helper->isIdentifier(new \stdClass()));
        $this->assertFalse($helper->isIdentifier(new class() {
        }));
    }

    public function testIsEmptyIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $emptyId = $this->createMock(DomainIdInterface::class);
        $emptyId->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);
        $id = $this->createMock(DomainIdInterface::class);
        $id->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);

        $this->assertTrue($helper->isEmptyIdentifier(null));
        $this->assertTrue($helper->isEmptyIdentifier($emptyId));
        $this->assertTrue($helper->isEmptyIdentifier(Entities\TestPrimitiveEntity::create()));
        $this->assertTrue($helper->isEmptyIdentifier(new \stdClass()));
        $this->assertFalse($helper->isEmptyIdentifier(Entities\TestCompositeEntity::create(['idB' => 'foo'])));
        $this->assertFalse($helper->isEmptyIdentifier($id));
        $this->assertFalse($helper->isEmptyIdentifier(1));
        $this->assertFalse($helper->isEmptyIdentifier('foo'));
        $this->assertFalse($helper->isEmptyIdentifier(new class() {
        }));
    }

    public function testNormalizeIdentifier(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

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

        $this->assertNull($helper->normalizeIdentifier(null));
        $this->assertNull($helper->normalizeIdentifier($emptyId));
        $this->assertNull($helper->normalizeIdentifier(Entities\TestPrimitiveEntity::create()));
        $this->assertNull($helper->normalizeIdentifier(new \stdClass()));
        $this->assertSame('id', $helper->normalizeIdentifier($id));
        $this->assertSame('id', $helper->normalizeIdentifier(Entities\TestPrimitiveEntity::create(['id' => 'id'])));
        $this->assertSame(['id'], $helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id])));
        $this->assertSame(['id', 'id-b'], $helper->normalizeIdentifier(Entities\TestCompositeEntity::create(['idA' => $id, 'idB' => 'id-b'])));
        $this->assertSame(1, $helper->normalizeIdentifier(1));
        $this->assertSame('foo', $helper->normalizeIdentifier('foo'));
        $this->assertSame($object = new class() {
        }, $helper->normalizeIdentifier($object));
    }

    public function testGetIdentifiers(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertSame(['foo'], $helper->getIdentifiers(Entities\TestPrimitiveEntity::create(['id' => 'foo'])));
        $this->assertSame(['bar'], $helper->getIdentifiers(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        $this->assertSame([], $helper->getIdentifiers(Entities\TestPrimitiveEntity::create()));
        $this->assertSame([], $helper->getIdentifiers(new \stdClass()));
    }

    public function testGetIdentifiersWithInvalidEntity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentifiers(new class() {
        });
    }

    public function testGetIdentifierFieldNames(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertSame(['id'], $helper->getIdentifierFieldNames(Entities\TestPrimitiveEntity::class));
        $this->assertSame(['idA', 'idB'], $helper->getIdentifierFieldNames(Entities\TestCompositeEntity::class));
        $this->assertSame([], $helper->getIdentifierFieldNames(\stdClass::class));
    }

    public function testGetIdentifierFieldNamesWithInvalidEntity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentifierFieldNames('foo');
    }

    public function testIsIdentity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertTrue($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => 'a', 'idB' => 'b']));
        $this->assertFalse($helper->isIdentity(Entities\TestCompositeEntity::class, ['idA' => 'a', 'idB' => null]));
        $this->assertTrue($helper->isIdentity(Entities\TestPrimitiveEntity::class, ['id' => 1]));
        $this->assertFalse($helper->isIdentity(Entities\TestPrimitiveEntity::class, []));
        $this->assertFalse($helper->isIdentity(Entities\TestPrimitiveEntity::class, ['foo' => 'bar']));
        $this->assertFalse($helper->isIdentity(Entities\TestPrimitiveEntity::class, ['id' => 1, 'foo' => 'bar']));
    }

    public function testIsIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->isIdentity('foo', ['id' => 1]);
    }

    public function testToIdentity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertSame(['idA' => 'a', 'idB' => 'b'], $helper->toIdentity(Entities\TestCompositeEntity::class, 'a', 'b'));
        $this->assertNull($helper->toIdentity(Entities\TestCompositeEntity::class, 'a', null));
        $this->assertSame(['id' => 1], $helper->toIdentity(Entities\TestPrimitiveEntity::class, 1));
        $this->assertNull($helper->toIdentity(Entities\TestPrimitiveEntity::class, null));
        $this->assertNull($helper->toIdentity(Entities\TestPrimitiveEntity::class, 1, 'foo'));
    }

    public function testToIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->toIdentity('foo', 1);
    }

    public function testGetIdentity(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->assertSame(['id' => 'foo'], $helper->getIdentity(Entities\TestPrimitiveEntity::create(['id' => 'foo'])));
        $this->assertSame(['idB' => 'bar'], $helper->getIdentity(Entities\TestCompositeEntity::create(['idB' => 'bar'])));
        $this->assertSame([], $helper->getIdentity(Entities\TestPrimitiveEntity::create()));
        $this->assertSame([], $helper->getIdentity(new \stdClass()));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $helper = new DomainIdentityHelper($this->mapping);

        $this->expectException(InvalidClassException::class);

        $helper->getIdentity(new class() {
        });
    }
}
