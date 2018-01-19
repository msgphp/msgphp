<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface DomainCollectionInterface extends \Countable, \IteratorAggregate
{
    public static function fromValue(?iterable $value): self;

    public function isEmpty(): bool;

    public function contains($element): bool;

    /**
     * @param string|int $key
     */
    public function containsKey($key): bool;

    public function first();

    public function last();

    /**
     * @param string|int $key
     */
    public function get($key);

    public function filter(callable $filter): self;

    public function slice(int $offset, int $limit = 0): self;

    public function map(callable $mapper): array;
}
