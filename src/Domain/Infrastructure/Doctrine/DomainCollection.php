<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\DomainCollection as BaseDomainCollection;
use MsgPhp\Domain\Exception\EmptyCollection;
use MsgPhp\Domain\Exception\UnknownCollectionElement;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @template TKey of array-key
 * @template T
 * @implements BaseDomainCollection<TKey, T>
 */
final class DomainCollection implements BaseDomainCollection
{
    /** @var Collection<TKey, T> */
    private $collection;

    /**
     * @param Collection<TKey, T> $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public static function fromValue(?iterable $value): BaseDomainCollection
    {
        if ($value instanceof Collection) {
            /** @var BaseDomainCollection */
            return new self($value);
        }

        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        /** @var BaseDomainCollection */
        return new self(new ArrayCollection($value ?? []));
    }

    public function getIterator(): \Traversable
    {
        return $this->collection->getIterator();
    }

    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }

    public function contains($element): bool
    {
        return $this->collection->contains($element);
    }

    public function containsKey($key): bool
    {
        return $this->collection->containsKey($key);
    }

    public function first()
    {
        if ($this->collection->isEmpty()) {
            throw EmptyCollection::create();
        }

        return $this->collection->first();
    }

    public function last()
    {
        if ($this->collection->isEmpty()) {
            throw EmptyCollection::create();
        }

        return $this->collection->last();
    }

    public function get($key)
    {
        if (!$this->collection->containsKey($key)) {
            throw UnknownCollectionElement::createForKey($key);
        }

        return $this->collection->get($key);
    }

    public function filter(callable $filter): BaseDomainCollection
    {
        return self::fromValue($this->collection->filter(\Closure::fromCallable($filter)));
    }

    public function slice(int $offset, int $limit = 0): BaseDomainCollection
    {
        return self::fromValue(new ArrayCollection($this->collection->slice($offset, $limit ?: null)));
    }

    public function map(callable $mapper): BaseDomainCollection
    {
        return self::fromValue($this->collection->map(\Closure::fromCallable($mapper)));
    }

    public function count(): int
    {
        return $this->collection->count();
    }
}
