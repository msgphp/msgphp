<?php

declare(strict_types=1);

namespace MsgPhp\User;

use MsgPhp\Domain\DomainIdTrait;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserId implements UserIdInterface
{
    use DomainIdTrait;
}
