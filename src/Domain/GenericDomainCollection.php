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
 * @implements DomainCollection<TKey,T>
 */
final class GenericDomainCollection implements DomainCollection
{
    private $elements;

    public function __construct(iterable $elements)
    {
        $this->elements = $elements;
    }

    public static function fromValue(?iterable $value): DomainCollection
    {
        /** @var DomainCollection */
        return new self($value ?? []);
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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->isEmpty();
        }

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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->contains($element);
        }

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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->containsKey($key);
        }

        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
                    return true;
                }
            }

            return false;
        }

        return isset($this->elements[$key]) || \array_key_exists($key, $this->elements);
    }

    public function first()
    {
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->first();
        }

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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->last();
        }

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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->get($key);
        }

        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->filter($filter);
        }

        if ($this->elements instanceof \Traversable) {
            return new self((function () use ($filter): iterable {
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
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->slice($offset, $limit);
        }

        if ($this->elements instanceof \Traversable) {
            return new self((function () use ($offset, $limit): iterable {
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

    public function map(callable $mapper): DomainCollection
    {
        if ($this->elements instanceof DomainCollection) {
            return $this->elements->map($mapper);
        }

        if ($this->elements instanceof \Traversable) {
            return new self((function () use ($mapper): iterable {
                foreach ($this->elements as $key => $element) {
                    yield $key => $mapper($element);
                }
            })());
        }

        return new self(array_map($mapper, $this->elements));
    }

    public function count(): int
    {
        if ($this->elements instanceof \Countable) {
            return $this->elements->count();
        }

        return $this->elements instanceof \Traversable ? iterator_count($this->elements) : \count($this->elements);
    }
}
