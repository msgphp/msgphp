<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

use MsgPhp\Domain\Exception\EmptyCollection;
use MsgPhp\Domain\Exception\UnknownCollectionElement;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @template TKey of array-key
 * @template T
 * @implements PaginatedDomainCollection<TKey,T>
 */
final class GenericDomainCollection implements PaginatedDomainCollection
{
    /** @var iterable<TKey, T> */
    private $elements;
    /** @var float */
    private $offset;
    /** @var float */
    private $limit;
    /** @var null|float */
    private $count;
    /** @var null|float */
    private $totalCount;

    /**
     * @param null|iterable<TKey, T> $elements
     */
    public function __construct(?iterable $elements = null, float $offset = .0, float $limit = .0, ?float $count = null, ?float $totalCount = null)
    {
        $this->elements = $elements ?? [];
        $this->offset = $offset;
        $this->limit = $limit;
        $this->count = $count;
        $this->totalCount = $totalCount;
    }

    public function getIterator(): \Traversable
    {
        if ($this->elements instanceof \IteratorAggregate) {
            return $this->elements->getIterator();
        }

        if ($this->elements instanceof \Traversable) {
            return (function (): \Traversable {
                foreach ($this->elements as $key => $element) {
                    yield $key => $element;
                }
            })();
        }

        return new \ArrayIterator($this->elements);
    }

    public function isEmpty(): bool
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $element) {
                return false;
            }

            return true;
        }

        return [] === $this->elements;
    }

    public function contains($element): bool
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $key => $knownElement) {
                if ($element === $knownElement) {
                    return true;
                }
            }

            return false;
        }

        return \in_array($element, $this->elements, true);
    }

    public function containsKey($key): bool
    {
        if ($this->elements instanceof \Traversable) {
            /** @psalm-suppress InvalidCast */
            $key = \is_int($key) ? (string) $key : $key;
            foreach ($this->elements as $knownKey => $element) {
                /** @psalm-suppress InvalidCast */
                if ($key === (\is_int($knownKey) ? (string) $knownKey : $knownKey)) {
                    return true;
                }
            }

            return false;
        }

        return isset($this->elements[$key]) || \array_key_exists($key, $this->elements);
    }

    public function first()
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $element) {
                return $element;
            }

            throw EmptyCollection::create();
        }

        if ([] === $this->elements) {
            throw EmptyCollection::create();
        }

        return reset($this->elements);
    }

    public function last()
    {
        if ($this->elements instanceof \Traversable) {
            $empty = true;
            $element = null;
            foreach ($this->elements as $key => $element) {
                $empty = false;
            }

            if ($empty) {
                throw EmptyCollection::create();
            }

            return $element;
        }

        if ([] === $this->elements) {
            throw EmptyCollection::create();
        }

        return end($this->elements);
    }

    public function get($key)
    {
        if ($this->elements instanceof \Traversable) {
            /** @psalm-suppress InvalidCast */
            $key = \is_int($key) ? (string) $key : $key;
            foreach ($this->elements as $knownKey => $element) {
                /** @psalm-suppress InvalidCast */
                if ($key === (\is_int($knownKey) ? (string) $knownKey : $knownKey)) {
                    return $element;
                }
            }

            throw UnknownCollectionElement::createForKey($key);
        }

        if (isset($this->elements[$key]) || \array_key_exists($key, $this->elements)) {
            return $this->elements[$key];
        }

        throw UnknownCollectionElement::createForKey($key);
    }

    public function filter(callable $filter): DomainCollection
    {
        if ($this->elements instanceof \Traversable) {
            return new self((/** @return \Generator<TKey, T> */function () use ($filter): iterable {
                foreach ($this->elements as $key => $element) {
                    if ($filter($element)) {
                        yield $key => $element;
                    }
                }
            })());
        }

        return new self(array_filter($this->elements, $filter));
    }

    public function slice(int $offset, int $limit = 0): DomainCollection
    {
        if ($this->elements instanceof \Traversable) {
            return new self((/** @return \Generator<TKey, T> */function () use ($offset, $limit): iterable {
                $i = -1;
                foreach ($this->elements as $key => $element) {
                    if (++$i < $offset) {
                        continue;
                    }

                    if ($limit && $i >= ($offset + $limit)) {
                        break;
                    }

                    yield $key => $element;
                }
            })());
        }

        return new self(\array_slice($this->elements, $offset, $limit ?: null, true));
    }

    /**
     * @template T2
     */
    public function map(callable $mapper): DomainCollection
    {
        if ($this->elements instanceof \Traversable) {
            return new self((/** @return \Generator<TKey, T2> */function () use ($mapper): iterable {
                foreach ($this->elements as $key => $element) {
                    yield $key => $mapper($element);
                }
            })());
        }

        return new self(array_map($mapper, $this->elements));
    }

    public function count(): int
    {
        if (null !== $this->count) {
            return (int) $this->count;
        }

        if ($this->elements instanceof \Countable) {
            return $this->elements->count();
        }

        return $this->elements instanceof \Traversable ? iterator_count($this->elements) : \count($this->elements);
    }

    public function getOffset(): float
    {
        return $this->offset;
    }

    public function getLimit(): float
    {
        return $this->limit;
    }

    public function getCurrentPage(): float
    {
        if (0 >= $this->limit) {
            return 1.;
        }

        return floor($this->offset / $this->limit) + 1.;
    }

    public function getLastPage(): float
    {
        if (0 >= $this->limit) {
            return 1.;
        }

        return ceil($this->getTotalCount() / $this->limit) ?: 1.;
    }

    public function getTotalCount(): float
    {
        return $this->totalCount ?? (float) \count($this);
    }
}
