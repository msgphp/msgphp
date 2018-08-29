<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Factory;

use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\{DomainCollection, DomainCollectionInterface};
use MsgPhp\Domain\Infra\Doctrine as DoctrineInfra;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollectionFactory
{
    public static function create(?iterable $value): DomainCollectionInterface
    {
        if ($value instanceof DomainCollectionInterface) {
            return $value;
        }

        if ($value instanceof Collection) {
            return new DoctrineInfra\DomainCollection($value);
        }

        return DomainCollection::fromValue($value);
    }

    public static function createFromCallable(callable $value): DomainCollectionInterface
    {
        $iterator = new class($value) implements \IteratorAggregate {
            private $callback;

            public function __construct(callable $value)
            {
                $this->callback = $value;
            }

            public function getIterator(): \Traversable
            {
                $iterable = ($this->callback)();

                return \is_array($iterable) ? new \ArrayIterator($iterable) : $iterable;
            }
        };

        return self::create($iterator);
    }

    private function __construct()
    {
    }
}
