<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @template TKey of array-key
 * @template T
 * @extends \IteratorAggregate<TKey, T>
 */
interface DomainCollection extends \Countable, \IteratorAggregate
{
    public function isEmpty(): bool;

    /**
     * @param T $element
     */
    public function contains($element): bool;

    /**
     * @param TKey $key
     */
    public function containsKey($key): bool;

    /**
     * @return T
     */
    public function first();

    /**
     * @return T
     */
    public function last();

    /**
     * @param TKey $key
     *
     * @return T
     */
    public function get($key);

    /**
     * @param callable(T):bool $filter
     *
     * @return self<TKey, T>
     */
    public function filter(callable $filter): self;

    /**
     * @return self<TKey, T>
     */
    public function slice(int $offset, int $limit = 0): self;

    /**
     * @template T2
     *
     * @param callable(T):T2 $mapper
     *
     * @return self<TKey, T2>
     */
    public function map(callable $mapper): self;
}
