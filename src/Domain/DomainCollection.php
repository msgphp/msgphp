<?php

declare(strict_types=1);

namespace MsgPhp\Domain;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollection implements DomainCollectionInterface
{
    private $elements;

    /**
     * @return $this|self
     */
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
        $this->toArray(true);

        if ($this->elements instanceof \Traversable) {
            return (function () {
                foreach ($this->elements as $element) {
                    yield $element;
                }
            })();
        }

        return new \ArrayIterator($this->elements);
    }

    public function isEmpty(): bool
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $element) {
                $this->toArray(true);

                return false;
            }

            return true;
        }

        return !$this->elements;
    }

    public function contains($element): bool
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $knownElement) {
                if ($element === $knownElement) {
                    $this->toArray(true);

                    return true;
                }
            }

            return false;
        }

        return in_array($element, $this->elements, true);
    }

    public function containsKey($key): bool
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
                    $this->toArray(true);

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
                $this->toArray(true);

                return $element;
            }

            return false;
        }

        return reset($this->elements);
    }

    public function last()
    {
        $this->toArray();

        return end($this->elements);
    }

    public function get($key)
    {
        if ($this->elements instanceof \Traversable) {
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
                    $this->toArray(true);

                    return $element;
                }
            }

            return null;
        }

        return $this->elements[$key] ?? null;
    }

    public function filter(callable $filter): DomainCollectionInterface
    {
        $this->toArray();

        return new self(array_filter($this->elements, $filter));
    }

    public function slice(int $offset, int $limit = 0): DomainCollectionInterface
    {
        $this->toArray();

        return new self(array_slice($this->elements, $offset, $limit ?: null, true));
    }

    public function map(callable $mapper): array
    {
        $this->toArray();

        return array_map($mapper, $this->elements);
    }

    public function count(): int
    {
        $this->toArray();

        return count($this->elements);
    }

    private function toArray(bool $generatorOnly = false): void
    {
        if ((!$generatorOnly && $this->elements instanceof \Traversable) || $this->elements instanceof \Generator) {
            $this->elements = iterator_to_array($this->elements);
        }
    }
}
