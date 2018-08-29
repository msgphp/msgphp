<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\PaginatedDomainCollection;
use MsgPhp\Domain\DomainCollectionInterface;

final class PaginatedDomainCollectionTest extends AbstractDomainCollectionTest
{
    public function testDefaultPagination(): void
    {
        $collection = new PaginatedDomainCollection([1, 2, 3, 4]);

        $this->assertSame(0., $collection->getOffset());
        $this->assertSame(0., $collection->getLimit());
        $this->assertSame(1., $collection->getCurrentPage());
        $this->assertSame(1., $collection->getLastPage());
        $this->assertSame(4., $collection->getTotalCount());
        $this->assertCount(4, $collection);
    }

    public function testPagination(): void
    {
        $collection = new PaginatedDomainCollection([], 8., 2., 2., 12.);

        $this->assertSame(8., $collection->getOffset());
        $this->assertSame(2., $collection->getLimit());
        $this->assertSame(5., $collection->getCurrentPage());
        $this->assertSame(6., $collection->getLastPage());
        $this->assertSame(12., $collection->getTotalCount());
        $this->assertCount(2, $collection);
    }

    protected static function createCollection(array $elements): DomainCollectionInterface
    {
        return new PaginatedDomainCollection($elements);
    }
}
