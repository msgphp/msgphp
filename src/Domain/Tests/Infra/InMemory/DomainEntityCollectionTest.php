<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Infra\InMemory\DomainCollection;
use PHPUnit\Framework\TestCase;

final class DomainEntityCollectionTest extends TestCase
{
    public function testIterator(): void
    {
        $collection = new DomainCollection($expected = [1, 2, 3]);

        $this->assertSame($expected, iterator_to_array($collection));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue((new DomainCollection([]))->isEmpty());
        $this->assertFalse((new DomainCollection([1]))->isEmpty());
    }

    public function testContains(): void
    {
        $this->assertTrue((new DomainCollection(['1', 1]))->contains(1));
        $this->assertFalse((new DomainCollection([1]))->contains('1'));
    }

    public function testFirst(): void
    {
        $this->assertFalse((new DomainCollection([]))->first());
        $this->assertSame(1, (new DomainCollection([1, 2]))->first());
    }

    public function testLast(): void
    {
        $this->assertFalse((new DomainCollection([]))->last());
        $this->assertSame(2, (new DomainCollection([1, 2]))->last());
    }

    public function testFilter(): void
    {
        $collection = new DomainCollection([1, 2, 3]);

        $this->assertSame([1, 3], array_values($result = iterator_to_array($collection->filter(function (int $i) {
            return 1 === $i || 3 === $i;
        }))));
        $this->assertSame([0, 2], array_keys($result));
    }

    public function testSlice(): void
    {
        $collection = new DomainCollection([1, 2, 3]);

        $this->assertSame([2, 3], array_values($result = iterator_to_array($collection->slice(1))));
        $this->assertSame([1, 2], array_keys($result));
        $this->assertSame([2], array_values($result = iterator_to_array($collection->slice(1, 1))));
        $this->assertSame([1], array_keys($result));
        $this->assertSame([1, 2], array_values($result = iterator_to_array($collection->slice(0, 2))));
        $this->assertSame([0, 1], array_keys($result));
    }

    public function testMap(): void
    {
        $collection = new DomainCollection([1, 2, 3]);

        $this->assertSame([2, 4, 6], array_values($result = $collection->map(function (int $i) {
            return $i * 2;
        })));
        $this->assertSame([0, 1, 2], array_keys($result));
    }

    public function testCount(): void
    {
        $this->assertCount(0, new DomainCollection([]));
        $this->assertCount(3, new DomainCollection([1, 2, 3]));
    }
}
