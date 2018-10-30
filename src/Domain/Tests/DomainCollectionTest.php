<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\DomainCollectionInterface;

final class DomainCollectionTest extends AbstractDomainCollectionTest
{
    public function testLazyFromValue(): void
    {
        self::assertEquals(new DomainCollection($generator = self::getGenerator([])), DomainCollection::fromValue($generator));
    }

    public function testLazyGetIterator(): void
    {
        self::assertSame([], iterator_to_array(self::createLazyCollection([])));
        self::assertSame($elements = [2, 'key' => 'val'], iterator_to_array($collection = self::createLazyCollection($elements, $visited)));
        self::assertSame($elements, $visited);

        $this->assertClosedGenerator();

        iterator_to_array($collection);
    }

    public function testLazyIsEmpty(): void
    {
        self::assertTrue(self::createLazyCollection([])->isEmpty());
        self::assertFalse(($collection = self::createLazyCollection([1, 2], $visited))->isEmpty());
        self::assertSame([1], $visited);
        self::assertFalse($collection->isEmpty());
        self::assertSame([1, 2], iterator_to_array($collection));

        $this->assertClosedGenerator();

        $collection->isEmpty();
    }

    public function testLazyContains(): void
    {
        self::assertFalse(static::createLazyCollection([])->contains(1));
        self::assertTrue(static::createLazyCollection([null], $visited)->contains(null));
        self::assertSame([null], $visited);
        self::assertTrue(($collection = static::createLazyCollection([1, '2'], $visited))->contains(1));
        self::assertSame([1], $visited);
        self::assertFalse($collection->contains(2));
        self::assertSame([1, '2'], $visited);

        $this->assertClosedGenerator();

        $collection->contains(null);
    }

    public function testLazyContainsKey(): void
    {
        self::assertFalse(static::createLazyCollection([])->containsKey(1));
        self::assertTrue(($collection = static::createLazyCollection([1, 'k' => 'v', '2' => null], $visited))->containsKey(2));
        self::assertSame([1, 'k' => 'v', '2' => null], $visited);

        $this->assertUnrewindableGenerator();

        $collection->containsKey(0);
    }

    public function testLazyFirst(): void
    {
        self::assertFalse(self::createLazyCollection([])->first());
        self::assertSame(1, ($collection = static::createLazyCollection([1, 2], $visited))->first());
        self::assertSame([1], $visited);
        self::assertSame(1, $collection->first());
        self::assertSame([1, 2], iterator_to_array($collection));

        $this->assertClosedGenerator();

        $collection->first();
    }

    public function testLazyLast(): void
    {
        self::assertFalse(self::createLazyCollection([])->last());
        self::assertSame(2, ($collection = static::createLazyCollection([1, 2], $visited))->last());
        self::assertSame([1, 2], $visited);

        $this->assertClosedGenerator();

        $collection->last();
    }

    public function testLazyGet(): void
    {
        self::assertNull(self::createLazyCollection([])->get('key'));
        self::assertSame(1, ($collection = static::createLazyCollection([1], $visited))->get(0));
        self::assertSame([1], $visited);
        self::assertSame(1, $collection->get('0'));
        self::assertNull($collection->get('foo'));

        $this->assertClosedGenerator();

        $collection->get('k');
    }

    public function testLazyFilter(): void
    {
        self::assertNotSame($collection = self::createLazyCollection([]), $filtered = $collection->filter(function (): bool {
            return true;
        }));
        self::assertSame([], iterator_to_array($filtered));
        self::assertSame([1, 2 => 3], iterator_to_array(($collection = static::createLazyCollection([1, null, 3], $visited))->filter($filter = function ($v): bool {
            return null !== $v;
        })));
        self::assertSame([1, null, 3], $visited);

        $result = $collection->filter($filter);

        $this->assertClosedGenerator();
        iterator_to_array($result);
    }

    public function testLazySlice(): void
    {
        self::assertNotSame($collection = self::createLazyCollection([]), $slice = $collection->slice(0));
        self::assertSame([], iterator_to_array($slice));
        self::assertSame([1 => 2], iterator_to_array(($collection = static::createLazyCollection([1, 2, 3, null, 5], $visited))->slice(1, 1)));
        self::assertSame([1, 2, 3], $visited);

        $result = $collection->slice(0);

        $this->assertUnrewindableGenerator();
        iterator_to_array($result);
    }

    public function testLazyMap(): void
    {
        self::assertSame([], iterator_to_array(self::createLazyCollection([])->map(function ($v) {
            return $v;
        })));
        self::assertSame([2], iterator_to_array($collection = self::createLazyCollection([1])->map($mapper = function (int $v): int {
            return $v * 2;
        })));

        $result = $collection->map($mapper);

        $this->assertClosedGenerator();
        iterator_to_array($result);
    }

    public function testLazyCount(): void
    {
        self::assertCount(0, self::createLazyCollection([]));
        self::assertCount(2, $collection = self::createLazyCollection([1, 2], $visited));
        self::assertSame([1, 2], $visited);

        $this->assertClosedGenerator();

        \count($collection);
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
