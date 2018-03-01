<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use MsgPhp\Domain\{DomainIdentityMappingInterface, DomainIdInterface};
use MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping;
use MsgPhp\Domain\Tests\AbstractDomainIdentityMappingTest;
use MsgPhp\Domain\Tests\Fixtures\Entities;

final class DomainIdentityMappingTest extends AbstractDomainIdentityMappingTest
{
    use EntityManagerTrait;

    private $createSchema = false;

    public function testClassMapping(): void
    {
        $this->assertSame(['id'], (new DomainIdentityMapping(self::$em, ['foo' => Entities\TestEntity::class]))->getIdentifierFieldNames('foo'));
    }

    protected static function createMapping(): DomainIdentityMappingInterface
    {
        return new DomainIdentityMapping(self::$em);
    }
}
