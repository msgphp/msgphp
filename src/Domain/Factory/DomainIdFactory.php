<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use MsgPhp\Domain\{DomainId, DomainIdInterface};
use MsgPhp\Domain\Infra\Uuid\DomainId as DomainUuid;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainIdFactory
{
    public static function create($value): DomainIdInterface
    {
        if ($value instanceof UuidInterface) {
            return new DomainUuid($value);
        }

        if (class_exists(Uuid::class) && Uuid::isValid($value)) {
            return DomainUuid::fromValue($value);
        }

        return DomainId::fromValue($value);
    }

    private function __construct()
    {
    }
}
