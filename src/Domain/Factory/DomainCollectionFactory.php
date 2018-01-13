<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\{DomainCollection, DomainCollectionInterface};
use MsgPhp\Domain\Infra\Doctrine\DomainCollection as DoctrineDomainCollection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollectionFactory
{
    public static function create(?iterable $value): DomainCollectionInterface
    {
        if ($value instanceof Collection) {
            return new DoctrineDomainCollection($value);
        }

        return DomainCollection::fromValue($value);
    }

    private function __construct()
    {
    }
}
