<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use MsgPhp\Domain\DomainIdentityMappingInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainIdentityMapping;
use MsgPhp\Domain\Tests\AbstractDomainIdentityMappingTest;

final class DomainIdentityMappingTest extends AbstractDomainIdentityMappingTest
{
    use EntityManagerTrait;

    private $createSchema = false;

    protected static function createMapping(): DomainIdentityMappingInterface
    {
        return new DomainIdentityMapping(self::$em);
    }
}
