<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

use MsgPhp\Domain\Exception\EmptyCollectionException;
use MsgPhp\Domain\Exception\UnknownCollectionElementException;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class GenericDomainCollection implements DomainCollectionInterface
{
    /**
     * @var iterable
     */
    private $elements;

    public function __construct(iterable $elements)
    {
        $this->elements = $elements;
    }

    public static function fromValue(?iterable $value): DomainCollectionInterface
    {
        return new self($value ?? []);
    }

    public function getIterator(): \Traversable
    {
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
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $element) {
                return $element;
            }

            throw EmptyCollectionException::create();
        }

        if ([] === $this->elements) {
            throw EmptyCollectionException::create();
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
                throw EmptyCollectionException::create();
            }

            return $element;
        }

        if ([] === $this->elements) {
            throw EmptyCollectionException::create();
        }

        return end($this->elements);
    }

    public function get($key)
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
                    return $element;
                }
            }

            throw UnknownCollectionElementException::createForKey($key);
        }

        if (isset($this->elements[$key]) || \array_key_exists($key, $this->elements)) {
            return $this->elements[$key];
        }

        throw UnknownCollectionElementException::createForKey($key);
    }

    public function filter(callable $filter): DomainCollectionInterface
    {
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

    public function slice(int $offset, int $limit = 0): DomainCollectionInterface
    {
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

    public function map(callable $mapper): DomainCollectionInterface
    {
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
        return $this->elements instanceof \Traversable ? iterator_count($this->elements) : \count($this->elements);
    }
}
