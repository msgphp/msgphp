<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\Domain\Exception\EmptyCollection;
use MsgPhp\Domain\Exception\UnknownCollectionElement;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\Domain\Tests\Fixtures\CountableIterator;

/**
 * @internal
 */
final class GenericDomainCollectionTest extends DomainCollectionTestCase
{
    public function testLazyGetIterator(): void
    {
        self::assertSame([], iterator_to_array(self::createLazyCollection([])));
        self::assertSame($elements = [2, 'key' => 'val'], iterator_to_array($collection = self::createLazyCollection($elements, $visited)));
        self::assertSame($elements, $visited);

        $this->assertClosedGenerator();

        iterator_to_array($collection);
    }

    public function testAggregatedGetIterator(): void
    {
        $iteratorAggregate = $this->createMock(\IteratorAggregate::class);
        $iteratorAggregate->expects(self::once())
            ->method('getIterator')
            ->willReturn($iterator = new \ArrayIterator([1]))
        ;

        self::assertSame($iterator, (new GenericDomainCollection($iteratorAggregate))->getIterator());
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
        /** @psalm-suppress InvalidArgument */
        self::assertFalse(self::createLazyCollection([])->contains(1));
        /** @psalm-suppress InvalidArgument */
        self::assertTrue(self::createLazyCollection([null], $visited)->contains(null));
        self::assertSame([null], $visited);
        /** @psalm-suppress InvalidArgument */
        self::assertTrue(($collection = self::createLazyCollection([1, '2'], $visited))->contains(1));
        self::assertSame([1], $visited);
        /** @psalm-suppress InvalidArgument */
        self::assertFalse($collection->contains(2));
        self::assertSame([1, '2'], $visited);

        $this->assertClosedGenerator();

        /** @psalm-suppress InvalidArgument */
        $collection->contains(null);
    }

    public function testLazyContainsKey(): void
    {
        /** @psalm-suppress InvalidArgument */
        self::assertFalse(self::createLazyCollection([])->containsKey(1));
        /** @psalm-suppress InvalidArgument */
        self::assertTrue(($collection = self::createLazyCollection([1, 'k' => 'v', '2' => null], $visited))->containsKey(2));
        self::assertSame([1, 'k' => 'v', '2' => null], $visited);

        $this->assertUnrewindableGenerator();

        /** @psalm-suppress InvalidArgument */
        $collection->containsKey(0);
    }

    public function testLazyFirst(): void
    {
        self::assertSame(1, ($collection = self::createLazyCollection([1, 2], $visited))->first());
        self::assertSame([1], $visited);
        self::assertSame(1, $collection->first());
        self::assertSame([1, 2], iterator_to_array($collection));

        $this->assertClosedGenerator();

        $collection->first();
    }

    public function testLazyFirstWithEmptyCollection(): void
    {
        $collection = self::createLazyCollection([]);

        $this->expectException(EmptyCollection::class);

        $collection->first();
    }

    public function testLazyLast(): void
    {
        self::assertSame(2, ($collection = self::createLazyCollection([1, 2], $visited))->last());
        self::assertSame([1, 2], $visited);

        $this->assertClosedGenerator();

        $collection->last();
    }

    public function testLazyLastWithEmptyCollection(): void
    {
        $collection = self::createLazyCollection([]);

        $this->expectException(EmptyCollection::class);

        $collection->last();
    }

    public function testLazyGet(): void
    {
        /** @psalm-suppress InvalidArgument */
        self::assertSame(1, ($collection = self::createLazyCollection([1, 2], $visited))->get(0));
        self::assertSame([1], $visited);
        /** @psalm-suppress InvalidArgument */
        self::assertSame(1, $collection->get('0'));
        /** @psalm-suppress InvalidArgument */
        self::assertSame(2, $collection->get(1));

        $this->assertUnrewindableGenerator();

        /** @psalm-suppress InvalidArgument */
        $collection->get(1);
    }

    public function testLazyGetWithEmptyCollection(): void
    {
        $collection = self::createLazyCollection([]);

        $this->expectException(UnknownCollectionElement::class);

        /** @psalm-suppress InvalidArgument */
        $collection->get('foo');
    }

    public function testLazyGetWithUnknownKey(): void
    {
        $collection = self::createLazyCollection(['bar' => 'foo', 1]);

        $this->expectException(UnknownCollectionElement::class);

        /** @psalm-suppress InvalidArgument */
        $collection->get('foo');
    }

    public function testLazyFilter(): void
    {
        self::assertNotSame($collection = self::createLazyCollection([]), $filtered = $collection->filter(static function (): bool {
            return true;
        }));
        self::assertSame([], iterator_to_array($filtered));
        self::assertSame([1, 2 => 3], iterator_to_array(($collection = self::createLazyCollection([1, null, 3], $visited))->filter($filter = static function ($v): bool {
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
        self::assertSame([1 => 2], iterator_to_array(($collection = self::createLazyCollection([1, 2, 3, null, 5], $visited))->slice(1, 1)));
        self::assertSame([1, 2, 3], $visited);

        $result = $collection->slice(0);

        $this->assertUnrewindableGenerator();
        iterator_to_array($result);
    }

    public function testLazyMap(): void
    {
        self::assertSame([], iterator_to_array(self::createLazyCollection([])->map(static function ($v) {
            return $v;
        })));
        self::assertSame([2], iterator_to_array($collection = self::createLazyCollection([1])->map($mapper = static function (int $v): int {
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

    public function testCountableCount(): void
    {
        $countable = $this->createMock(CountableIterator::class);
        $countable->expects(self::once())
            ->method('count')
            ->willReturn(123)
        ;

        self::assertCount(123, new GenericDomainCollection($countable));
    }

    public function testDefaultPagination(): void
    {
        $collection = new GenericDomainCollection([1, 2, 3, 4]);

        self::assertSame(0., $collection->getOffset());
        self::assertSame(0., $collection->getLimit());
        self::assertSame(1., $collection->getCurrentPage());
        self::assertSame(1., $collection->getLastPage());
        self::assertSame(4., $collection->getTotalCount());
        self::assertCount(4, $collection);
    }

    public function testPagination(): void
    {
        $collection = new GenericDomainCollection([1, 2, 3, 4], 8., 2., 2., 12.);

        self::assertSame(8., $collection->getOffset());
        self::assertSame(2., $collection->getLimit());
        self::assertSame(5., $collection->getCurrentPage());
        self::assertSame(6., $collection->getLastPage());
        self::assertSame(12., $collection->getTotalCount());
        self::assertCount(2, $collection);
    }

    protected static function createCollection(array $elements): DomainCollection
    {
        return new GenericDomainCollection($elements);
    }

    private static function createLazyCollection(array $elements, ?array &$visited = null): GenericDomainCollection
    {
        return new GenericDomainCollection(self::getGenerator($elements, $visited));
    }

    private static function getGenerator(array $elements, ?array &$visited = null): \Generator
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
