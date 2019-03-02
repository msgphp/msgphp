<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Uuid;

use MsgPhp\Domain\Infra\Uuid\DomainIdTrait;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserUuid implements UserIdInterface
{
    use DomainIdTrait;
}
