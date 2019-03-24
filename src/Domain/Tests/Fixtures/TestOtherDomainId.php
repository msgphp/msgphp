<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\DomainIdTrait;

final class TestOtherDomainId implements DomainId
{
    use DomainIdTrait;
}
