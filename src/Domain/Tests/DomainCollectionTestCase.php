<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Exception\EmptyCollection;
use MsgPhp\Domain\Exception\UnknownCollectionElement;
use PHPUnit\Framework\TestCase;

abstract class DomainCollectionTestCase extends TestCase
{
    public function testGetIterator(): void
    {
        self::assertSame([], iterator_to_array(static::createCollection([])));
        self::assertSame([1], iterator_to_array(static::createCollection([1])));
        self::assertSame([null], iterator_to_array(static::createCollection([null])));
        self::assertSame(['k' => 'v'], iterator_to_array(static::createCollection(['k' => 'v'])));
    }

    public function testIsEmpty(): void
    {
        self::assertTrue(static::createCollection([])->isEmpty());
        self::assertFalse(static::createCollection([1])->isEmpty());
        self::assertFalse(static::createCollection([null])->isEmpty());
    }

    public function testContains(): void
    {
        self::assertFalse(static::createCollection([])->contains(1));
        self::assertTrue(static::createCollection([null])->contains(null));
        self::assertTrue(($collection = static::createCollection([1, '2']))->contains(1));
        self::assertFalse($collection->contains(2));
        self::assertFalse($collection->contains(null));
    }

    public function testContainsKey(): void
    {
        self::assertFalse(static::createCollection([])->containsKey(1));
        self::assertTrue(($collection = static::createCollection([1, 'k' => 'v', '2' => null]))->containsKey(0));
        self::assertTrue($collection->containsKey('k'));
        self::assertTrue($collection->containsKey(2));
        self::assertTrue($collection->containsKey('2'));
        self::assertFalse($collection->containsKey(1));
    }

    public function testFirst(): void
    {
        self::assertSame(1, static::createCollection([1])->first());
        self::assertSame(1, static::createCollection([1, 2])->first());
        self::assertNull(static::createCollection([null, 2])->first());
    }

    public function testFirstWithEmptyCollection(): void
    {
        $collection = static::createCollection([]);

        $this->expectException(EmptyCollection::class);

        $collection->first();
    }

    public function testLast(): void
    {
        self::assertSame(1, static::createCollection([1])->last());
        self::assertSame(2, static::createCollection([1, 2])->last());
        self::assertNull(static::createCollection([1, null])->last());
    }

    public function testLastWithEmptyCollection(): void
    {
        $collection = static::createCollection([]);

        $this->expectException(EmptyCollection::class);

        $collection->last();
    }

    public function testGet(): void
    {
        self::assertSame(1, ($collection = static::createCollection([1, 'k' => 'v', 2 => null]))->get(0));
        self::assertSame(1, $collection->get('0'));
        self::assertNull($collection->get(2));
        self::assertNull($collection->get('2'));
    }

    public function testGetWithEmptyCollection(): void
    {
        $collection = static::createCollection([]);

        $this->expectException(UnknownCollectionElement::class);

        $collection->get(0);
    }

    public function testGetWithUnknownKey(): void
    {
        $collection = static::createCollection(['bar' => 'foo', 1]);

        $this->expectException(UnknownCollectionElement::class);

        $collection->get('foo');
    }

    public function testFilter(): void
    {
        self::assertNotSame($collection = static::createCollection([]), $filtered = $collection->filter(static function (): bool {
            return true;
        }));
        self::assertSame([], iterator_to_array($filtered));
        self::assertSame([1, 2 => 3], iterator_to_array(static::createCollection([1, null, 3])->filter(static function ($v): bool {
            return null !== $v;
        })));
    }

    public function testSlice(): void
    {
        self::assertNotSame($collection = static::createCollection([]), $slice = $collection->slice(0));
        self::assertSame([], iterator_to_array($slice));
        self::assertSame([3 => null, 4 => 5], iterator_to_array(($collection = static::createCollection([1, 2, 3, null, 5]))->slice(3)));
        self::assertSame([1], iterator_to_array($collection->slice(0, 1)));
        self::assertSame([1 => 2], iterator_to_array($collection->slice(1, 1)));
        self::assertSame([4 => 5], iterator_to_array($collection->slice(4, 20)));
        self::assertSame([], iterator_to_array($collection->slice(15, 20)));
    }

    public function testMap(): void
    {
        self::assertSame([], iterator_to_array(static::createCollection([])->map(static function ($v) {
            return $v;
        })));
        self::assertSame(['k' => 2, 4, 6], iterator_to_array(static::createCollection(['k' => 1, 2, 3])->map(static function (int $v): int {
            return $v * 2;
        })));
    }

    public function testCount(): void
    {
        self::assertCount(0, static::createCollection([]));
        self::assertCount(1, static::createCollection([null]));
        self::assertCount(3, static::createCollection([1, 'k' => null, 2]));
    }

    abstract protected static function createCollection(array $elements): DomainCollection;
}
