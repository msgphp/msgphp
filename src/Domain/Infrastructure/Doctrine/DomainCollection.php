<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Exception\EmptyCollectionException;
use MsgPhp\Domain\Exception\UnknownCollectionElementException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollection implements DomainCollectionInterface
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

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
            throw EmptyCollectionException::create();
        }

        return $this->collection->first();
    }

    public function last()
    {
        if ($this->collection->isEmpty()) {
            throw EmptyCollectionException::create();
        }

        return $this->collection->last();
    }

    public function get($key)
    {
        if (!$this->collection->containsKey($key)) {
            throw UnknownCollectionElementException::createForKey($key);
        }

        return $this->collection->get($key);
    }

    public function filter(callable $filter): DomainCollectionInterface
    {
        return new self($this->collection->filter(\Closure::fromCallable($filter)));
    }

    public function slice(int $offset, int $limit = 0): DomainCollectionInterface
    {
        return new self(new ArrayCollection($this->collection->slice($offset, $limit ?: null)));
    }

    public function map(callable $mapper): DomainCollectionInterface
    {
        return new self($this->collection->map(\Closure::fromCallable($mapper)));
    }

    public function count(): int
    {
        return $this->collection->count();
    }
}
