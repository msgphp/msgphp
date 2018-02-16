<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollectionInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDomainCollectionTest extends TestCase
{
    public function testFromValue(): void
    {
        $class = get_class(static::createCollection([]));

        $this->assertSame([], iterator_to_array($class::fromValue(null)));
        $this->assertSame([1], iterator_to_array($class::fromValue([1])));
        $this->assertSame([1, 'k' => '2'], iterator_to_array($class::fromValue([1, 'k' => '2'])));
        $this->assertSame([1, 2, 3], iterator_to_array($class::fromValue(new \ArrayIterator([1, 2, 3]))));
        $this->assertEquals(['k' => 1, 2, '3' => 'v'], iterator_to_array($class::fromValue((function () {
            yield from ['k' => 1, 2];
            yield '3' => 'v';
        })())));
    }

    public function testGetIterator(): void
    {
        $this->assertSame([], iterator_to_array(static::createCollection([])));
        $this->assertSame([1], iterator_to_array(static::createCollection([1])));
        $this->assertSame([null], iterator_to_array(static::createCollection([null])));
        $this->assertSame(['k' => 'v'], iterator_to_array(static::createCollection(['k' => 'v'])));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(static::createCollection([])->isEmpty());
        $this->assertFalse(static::createCollection([1])->isEmpty());
        $this->assertFalse(static::createCollection([null])->isEmpty());
    }

    public function testContains(): void
    {
        $this->assertFalse(static::createCollection([])->contains(1));
        $this->assertTrue(static::createCollection([null])->contains(null));
        $this->assertTrue(($collection = static::createCollection([1, '2']))->contains(1));
        $this->assertFalse($collection->contains(2));
        $this->assertFalse($collection->contains(null));
    }

    public function testContainsKey(): void
    {
        $this->assertFalse(static::createCollection([])->containsKey(1));
        $this->assertTrue(($collection = static::createCollection([1, 'k' => 'v', '2' => null]))->containsKey(0));
        $this->assertTrue($collection->containsKey('k'));
        $this->assertTrue($collection->containsKey(2));
        $this->assertTrue($collection->containsKey('2'));
        $this->assertFalse($collection->containsKey(1));
    }

    public function testFirst(): void
    {
        $this->assertFalse(static::createCollection([])->first());
        $this->assertSame(1, static::createCollection([1])->first());
        $this->assertSame(1, static::createCollection([1, 2])->first());
        $this->assertNull(static::createCollection([null, 2])->first());
    }

    public function testLast(): void
    {
        $this->assertFalse(static::createCollection([])->last());
        $this->assertSame(1, static::createCollection([1])->last());
        $this->assertSame(2, static::createCollection([1, 2])->last());
        $this->assertNull(static::createCollection([1, null])->last());
    }

    public function testGet(): void
    {
        $this->assertNull(static::createCollection([])->get(0));
        $this->assertNull(static::createCollection([])->get('k'));
        $this->assertSame(1, ($collection = static::createCollection([1, 'k' => 'v', 2 => null]))->get(0));
        $this->assertSame(1, $collection->get('0'));
        $this->assertNull($collection->get(2));
        $this->assertNull($collection->get('2'));
    }

    public function testFilter(): void
    {
        $this->assertNotSame($collection = static::createCollection([]), $filtered = $collection->filter(function (): bool {
            return true;
        }));
        $this->assertSame([], iterator_to_array($filtered));
        $this->assertSame([1, 2 => 3], iterator_to_array(static::createCollection([1, null, 3])->filter(function ($v): bool {
            return null !== $v;
        })));
    }

    public function testSlice(): void
    {
        $this->assertNotSame($collection = static::createCollection([]), $slice = $collection->slice(0));
        $this->assertSame([], iterator_to_array($slice));
        $this->assertSame([3 => null, 4 => 5], iterator_to_array(($collection = static::createCollection([1, 2, 3, null, 5]))->slice(3)));
        $this->assertSame([1], iterator_to_array($collection->slice(0, 1)));
        $this->assertSame([1 => 2], iterator_to_array($collection->slice(1, 1)));
        $this->assertSame([4 => 5], iterator_to_array($collection->slice(4, 20)));
        $this->assertSame([], iterator_to_array($collection->slice(15, 20)));
    }

    public function testMap(): void
    {
        $this->assertSame([], static::createCollection([])->map(function ($v) {
            return $v;
        }));
        $this->assertSame(['k' => 2, 4, 6], static::createCollection(['k' => 1, 2, 3])->map(function (int $v): int {
            return $v * 2;
        }));
    }

    public function testCount(): void
    {
        $this->assertCount(0, static::createCollection([]));
        $this->assertCount(1, static::createCollection([null]));
        $this->assertCount(3, static::createCollection([1, 'k' => null, 2]));
    }

    abstract protected static function createCollection(array $elements): DomainCollectionInterface;
}
