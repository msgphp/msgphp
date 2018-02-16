<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\DomainCollectionInterface;

final class DomainCollectionTest extends AbstractDomainCollectionTest
{
    public function testLazyFromValue(): void
    {
        $this->assertEquals(new DomainCollection($generator = self::getGenerator([])), DomainCollection::fromValue($generator));
    }

    public function testLazyGetIterator(): void
    {
        $this->assertSame([], iterator_to_array(self::createLazyCollection([])));
        $this->assertSame($elements = [2, 'key' => 'val'], iterator_to_array($collection = self::createLazyCollection($elements, $visited)));
        $this->assertSame($elements, $visited);

        $this->assertClosedGenerator();

        iterator_to_array($collection);
    }

    public function testLazyIsEmpty(): void
    {
        $this->assertTrue(self::createLazyCollection([])->isEmpty());
        $this->assertFalse(($collection = self::createLazyCollection([1, 2], $visited))->isEmpty());
        $this->assertSame([1], $visited);
        $this->assertFalse($collection->isEmpty());
        $this->assertSame([1, 2], iterator_to_array($collection));

        $this->assertClosedGenerator();

        $collection->isEmpty();
    }

    public function testLazyContains(): void
    {
        $this->assertFalse(static::createLazyCollection([])->contains(1));
        $this->assertTrue(static::createLazyCollection([null], $visited)->contains(null));
        $this->assertSame([null], $visited);
        $this->assertTrue(($collection = static::createLazyCollection([1, '2'], $visited))->contains(1));
        $this->assertSame([1], $visited);
        $this->assertFalse($collection->contains(2));
        $this->assertSame([1, '2'], $visited);

        $this->assertClosedGenerator();

        $collection->contains(null);
    }

    public function testLazyContainsKey(): void
    {
        $this->assertFalse(static::createLazyCollection([])->containsKey(1));
        $this->assertTrue(($collection = static::createLazyCollection([1, 'k' => 'v', '2' => null], $visited))->containsKey(2));
        $this->assertSame([1, 'k' => 'v', '2' => null], $visited);

        $this->assertUnrewindableGenerator();

        $collection->containsKey(0);
    }

    public function testLazyFirst(): void
    {
        $this->assertFalse(self::createLazyCollection([])->first());
        $this->assertSame(1, ($collection = static::createLazyCollection([1, 2], $visited))->first());
        $this->assertSame([1], $visited);
        $this->assertSame(1, $collection->first());
        $this->assertSame([1, 2], iterator_to_array($collection));

        $this->assertClosedGenerator();

        $collection->first();
    }

    public function testLazyLast(): void
    {
        $this->assertFalse(self::createLazyCollection([])->last());
        $this->assertSame(2, ($collection = static::createLazyCollection([1, 2], $visited))->last());
        $this->assertSame([1, 2], $visited);

        $this->assertClosedGenerator();

        $collection->last();
    }

    public function testLazyGet(): void
    {
        $this->assertNull(self::createLazyCollection([])->get('key'));
        $this->assertSame(1, ($collection = static::createLazyCollection([1], $visited))->get(0));
        $this->assertSame([1], $visited);
        $this->assertSame(1, $collection->get('0'));
        $this->assertNull($collection->get('foo'));

        $this->assertClosedGenerator();

        $collection->get('k');
    }

    public function testLazyFilter(): void
    {
        $this->assertNotSame($collection = self::createLazyCollection([]), $filtered = $collection->filter(function (): bool {
            return true;
        }));
        $this->assertSame([], iterator_to_array($filtered));
        $this->assertSame([1, 2 => 3], iterator_to_array(($collection = static::createLazyCollection([1, null, 3], $visited))->filter($filter = function ($v): bool {
            return null !== $v;
        })));
        $this->assertSame([1, null, 3], $visited);

        $this->assertClosedGenerator();

        $collection->filter($filter);
    }

    public function testLazySlice(): void
    {
        $this->assertNotSame($collection = self::createLazyCollection([]), $slice = $collection->slice(0));
        $this->assertSame([], iterator_to_array($slice));
        $this->assertSame([1 => 2], iterator_to_array(($collection = static::createLazyCollection([1, 2, 3, null, 5], $visited))->slice(1, 1)));
        $this->assertSame([1, 2, 3], $visited);

        $this->assertUnrewindableGenerator();

        $collection->slice(0);
    }

    public function testLazyMap(): void
    {
        $this->assertSame([], self::createLazyCollection([])->map(function ($v) {
            return $v;
        }));
        $this->assertSame([2], ($collection = self::createLazyCollection([1]))->map($mapper = function (int $v): int {
            return $v * 2;
        }));

        $this->assertClosedGenerator();

        $collection->map($mapper);
    }

    public function testLazyCount(): void
    {
        $this->assertCount(0, self::createLazyCollection([]));
        $this->assertCount(2, $collection = self::createLazyCollection([1, 2], $visited));
        $this->assertSame([1, 2], $visited);

        $this->assertClosedGenerator();

        count($collection);
    }

    protected static function createCollection(array $elements): DomainCollectionInterface
    {
        return new DomainCollection($elements);
    }

    private static function createLazyCollection(array $elements, array &$visited = null): DomainCollection
    {
        return new DomainCollection(self::getGenerator($elements, $visited));
    }

    private static function getGenerator(array $elements, array &$visited = null): \Generator
    {
        $visited = [];

        foreach ($elements as $k => $v) {
            $visited[$k] = $v;

            yield $k => $v;
        }
    }

    private function assertClosedGenerator(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot traverse an already closed generator');
    }

    private function assertUnrewindableGenerator(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot rewind a generator that was already run');
    }
}
