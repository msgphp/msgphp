<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\Infra\Doctrine\DomainIdType;

class TestDomainIdType extends DomainIdType
{
    public const NAME = 'domain_id';
}
