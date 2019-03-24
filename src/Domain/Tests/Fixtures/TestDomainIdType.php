<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\Infrastructure\Doctrine\DomainIdType;

class TestDomainIdType extends DomainIdType
{
    public const NAME = 'domain_id';
}
