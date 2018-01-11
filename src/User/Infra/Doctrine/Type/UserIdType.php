<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Type;

use MsgPhp\Domain\Infra\Doctrine\DomainIdType;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserIdType extends DomainIdType
{
    public const NAME = 'msgphp_user_id';
}
