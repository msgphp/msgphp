<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollection implements DomainCollectionInterface
{
    private $elements;

    public static function fromValue(?iterable $value): DomainCollectionInterface
    {
        return new self($value ?? []);
    }

    public function __construct(iterable $elements)
    {
        $this->elements = $elements;
    }

    public function getIterator(): \Traversable
    {
        if ($this->elements instanceof \Traversable) {
            return (function () {
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

        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    public function first()
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $element) {
                return $element;
            }

            return false;
        }

        return reset($this->elements);
    }

    public function last()
    {
        if ($this->elements instanceof \Traversable) {
            $element = false;
            foreach ($this->elements as $key => $element) {
            }

            return $element;
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

            return null;
        }

        return $this->elements[$key] ?? null;
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
