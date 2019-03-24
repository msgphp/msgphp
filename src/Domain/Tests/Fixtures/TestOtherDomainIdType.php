<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\Infrastructure\Doctrine\DomainIdType;

class TestOtherDomainIdType extends DomainIdType
{
    public const NAME = 'other_domain_id';
}
