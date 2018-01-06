<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine\Type;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainUuidType;
use MsgPhp\User\Infra\Uuid\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserIdType extends DomainUuidType
{
    public const NAME = 'msgphp_user_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function convertToDomainId(string $value): DomainIdInterface
    {
        return new UserId($value);
    }
}
