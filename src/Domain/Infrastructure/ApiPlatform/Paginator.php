<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\ApiPlatform;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use MsgPhp\Domain\PaginatedDomainCollectionInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Paginator implements \IteratorAggregate, PaginatorInterface
{
    /**
     * @var PaginatedDomainCollectionInterface
     */
    private $collection;

    public function __construct(PaginatedDomainCollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    public function getIterator(): \Traversable
    {
        return $this->collection->getIterator();
    }

    public function count(): int
    {
        return \count($this->collection);
    }

    public function getLastPage(): float
    {
        return $this->collection->getLastPage();
    }

    public function getTotalItems(): float
    {
        return $this->collection->getTotalCount();
    }

    public function getCurrentPage(): float
    {
        return $this->collection->getCurrentPage();
    }

    public function getItemsPerPage(): float
    {
        return $this->collection->getLimit();
    }
}
