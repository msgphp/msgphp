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
        if ($this->elements instanceof \Traversable) {
            return (function () {
                $elements = [];
                foreach ($this->elements as $key => $element) {
                    yield $key => $element;

                    $elements[$key] = $element;
                }

                $this->elements = $elements;
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

            $this->elements = [];

            return true;
        }

        return !$this->elements;
    }

    public function contains($element): bool
    {
        if ($this->elements instanceof \Traversable) {
            $elements = [];
            foreach ($this->elements as $key => $knownElement) {
                if ($element === $knownElement) {
                    return true;
                }

                $elements[$key] = $knownElement;
            }

            if ($this->elements instanceof \Generator) {
                $this->elements = $elements;
            }

            return false;
        }

        return in_array($element, $this->elements, true);
    }

    public function containsKey($key): bool
    {
        if ($this->elements instanceof \Traversable) {
            $elements = [];
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
                    return true;
                }

                $elements[$knownKey] = $element;
            }

            if ($this->elements instanceof \Generator) {
                $this->elements = $elements;
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

            $this->elements = [];

            return false;
        }

        return reset($this->elements);
    }

    public function last()
    {
        if ($this->elements instanceof \Traversable) {
            $elements = [];
            $element = null;
            foreach ($this->elements as $key => $element) {
                $elements[$key] = $element;
            }

            if ($this->elements instanceof \Generator) {
                $this->elements = $elements;
            }

            return $elements ? $element : false;
        }

        return end($this->elements);
    }

    public function get($key)
    {
        if ($this->elements instanceof \Traversable) {
            $elements = [];
            foreach ($this->elements as $knownKey => $element) {
                if ((string) $key === (string) $knownKey) {
                    return $element;
                }

                $elements[$knownKey] = $element;
            }

            if ($this->elements instanceof \Generator) {
                $this->elements = $elements;
            }

            return null;
        }

        return $this->elements[$key] ?? null;
    }

    public function filter(callable $filter): DomainCollectionInterface
    {
        if ($this->elements instanceof \Traversable) {
            $elements = $filtered = [];
            foreach ($this->elements as $key => $element) {
                $elements[$key] = $element;

                if ($filter($element)) {
                    $filtered[$key] = $element;
                }
            }

            if ($this->elements instanceof \Generator) {
                $this->elements = $elements;
            }

            return new self($filtered);
        }

        return new self(array_filter($this->elements, $filter));
    }

    public function slice(int $offset, int $limit = 0): DomainCollectionInterface
    {
        if ($this->elements instanceof \Traversable) {
            $elements = $slice = [];
            $i = -1;
            $break = false;
            foreach ($this->elements as $key => $element) {
                $elements[$key] = $element;

                if (++$i < $offset) {
                    continue;
                }

                $slice[$key] = $element;

                if ($limit && $i >= ($offset + $limit)) {
                    $break = true;
                    break;
                }
            }

            if (!$break && $this->elements instanceof \Generator) {
                $this->elements = $elements;
            }

            return new self($slice);
        }

        return new self(array_slice($this->elements, $offset, $limit ?: null, true));
    }

    public function map(callable $mapper): array
    {
        if ($this->elements instanceof \Traversable) {
            $elements = $mapped = [];
            foreach ($this->elements as $key => $element) {
                $elements[$key] = $element;
                $mapped[$key] = $mapper($element);
            }

            if ($this->elements instanceof \Generator) {
                $this->elements = $elements;
            }

            return $mapped;
        }

        return array_map($mapper, $this->elements);
    }

    public function count(): int
    {
        if ($this->elements instanceof \Generator) {
            return count($this->elements = iterator_to_array($this->elements));
        }

        if ($this->elements instanceof \Traversable) {
            return iterator_count($this->elements);
        }

        return count($this->elements);
    }
}
