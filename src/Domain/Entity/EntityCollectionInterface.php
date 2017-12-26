<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Entity;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface EntityCollectionInterface extends \Countable, \IteratorAggregate
{
    public function isEmpty(): bool;

    public function contains($entity): bool;

    public function first();

    public function last();

    public function filter(callable $filter): self;

    public function map(callable $mapper): array;
}
