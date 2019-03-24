<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Fixtures;

use MsgPhp\Domain\DomainId;
use MsgPhp\Domain\Infrastructure\Uuid\DomainIdTrait;

final class TestOtherDomainUuid implements DomainId
{
    use DomainIdTrait;
}
