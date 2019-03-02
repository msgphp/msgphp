<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\{DomainIdInterface, DomainIdTrait};

final class TestOtherDomainId implements DomainIdInterface
{
    use DomainIdTrait;
}
