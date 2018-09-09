<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\DomainIdentityMappingInterface;
use MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping;
use MsgPhp\Domain\Tests\AbstractDomainIdentityMappingTest;
use MsgPhp\Domain\Tests\Fixtures\Entities;

final class DomainIdentityMappingTest extends AbstractDomainIdentityMappingTest
{
    public function testGetIdentifierFieldNamesCastsMapping(): void
    {
        $mapping = new DomainIdentityMapping(['foo' => 'a', 'bar' => ['b']]);

        self::assertSame(['a'], $mapping->getIdentifierFieldNames('foo'));
        self::assertSame(['b'], $mapping->getIdentifierFieldNames('bar'));
    }

    public function testGetIdentifierFieldNamesWithEmptyMapping(): void
    {
        $mapping = new DomainIdentityMapping(['foo' => []]);

        $this->expectException(\LogicException::class);

        $mapping->getIdentifierFieldNames('foo');
    }

    protected static function createMapping(): DomainIdentityMappingInterface
    {
        return new DomainIdentityMapping([
            Entities\TestCompositeEntity::class => Entities\TestCompositeEntity::getIdFields(),
            Entities\TestDerivedCompositeEntity::class => Entities\TestDerivedCompositeEntity::getIdFields(),
            Entities\TestDerivedEntity::class => Entities\TestDerivedEntity::getIdFields(),
            Entities\TestEntity::class => Entities\TestEntity::getIdFields(),
            Entities\TestPrimitiveEntity::class => Entities\TestPrimitiveEntity::getIdFields(),
        ]);
    }
}
