<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\DomainCollectionInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollection implements DomainCollectionInterface
{
    private $collection;

    /**
     * @return $this|self
     */
    public static function fromValue(?iterable $value): DomainCollectionInterface
    {
        if ($value instanceof Collection) {
            return new self($value);
        }

        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        return new self(new ArrayCollection($value ?? []));
    }

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->collection->toArray());
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
        return $this->collection->first();
    }

    public function last()
    {
        return $this->collection->last();
    }

    public function get($key)
    {
        return $this->collection->get($key);
    }

    public function filter(callable $filter): DomainCollectionInterface
    {
        return new self($this->collection->filter($filter));
    }

    public function slice(int $offset, int $limit = 0): DomainCollectionInterface
    {
        return new self(new ArrayCollection($this->collection->slice($offset, $limit ?: null)));
    }

    public function map(callable $mapper): array
    {
        return $this->collection->map($mapper)->toArray();
    }

    public function count(): int
    {
        return $this->collection->count();
    }
}
