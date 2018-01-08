<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\InMemory;

use MsgPhp\Domain\DomainCollectionInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainCollection implements DomainCollectionInterface
{
    private $elements;

    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function isEmpty(): bool
    {
        return !$this->elements;
    }

    public function contains($element): bool
    {
        return in_array($element, $this->elements, true);
    }

    public function first()
    {
        return reset($this->elements);
    }

    public function last()
    {
        return end($this->elements);
    }

    public function filter(callable $filter): DomainCollectionInterface
    {
        return new self(array_filter($this->elements, $filter));
    }

    public function slice(int $offset, int $limit = 0): DomainCollectionInterface
    {
        return new self(array_slice($this->elements, $offset, $limit ?: null, true));
    }

    public function map(callable $mapper): array
    {
        return array_map($mapper, $this->elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
