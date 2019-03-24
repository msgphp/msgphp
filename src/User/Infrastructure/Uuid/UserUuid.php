<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Uuid;

use MsgPhp\Domain\Infrastructure\Uuid\DomainIdTrait;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserUuid implements UserId
{
    use DomainIdTrait;
}
