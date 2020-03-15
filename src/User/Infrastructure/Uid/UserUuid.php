<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Uid;

use MsgPhp\Domain\Infrastructure\Uid\DomainIdTrait;
use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserUuid implements UserId
{
    use DomainIdTrait;
}
