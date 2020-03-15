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
    public function testLazyFromValue(): void
    {
        self::assertEquals(new GenericDomainCollection($generator = self::getGenerator([])), GenericDomainCollection::fromValue($generator));
    }

    public function testLazyGetIterator(): void
    {
        self::assertSame([], iterator_to_array(self::createLazyCollection([])));
        self::assertSame($elements = [2, 'key' => 'val'], iterator_to_array($collection = self::createLazyCollection($elements, $visited)));
        self::assertSame($elements, $visited);

        $this->assertClosedGenerator();

        iterator_to_array($collection);
    }

    public function testDecoratedGetIterator(): void
    {
        $iterator = $this->createMock(\IteratorAggregate::class);
        $iterator->expects(self::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([1]))
        ;

        self::assertSame([1], iterator_to_array(new GenericDomainCollection($iterator)));
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

    public function testDecoratedIsEmpty(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('isEmpty')
            ->willReturn(true)
        ;

        self::assertTrue((new GenericDomainCollection($collection))->isEmpty());
    }

    public function testLazyContains(): void
    {
        self::assertFalse(self::createLazyCollection([])->contains(1));
        self::assertTrue(self::createLazyCollection([null], $visited)->contains(null));
        self::assertSame([null], $visited);
        self::assertTrue(($collection = self::createLazyCollection([1, '2'], $visited))->contains(1));
        self::assertSame([1], $visited);
        self::assertFalse($collection->contains(2));
        self::assertSame([1, '2'], $visited);

        $this->assertClosedGenerator();

        $collection->contains(null);
    }

    public function testDecoratedContains(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('contains')
            ->with(1)
            ->willReturn(false)
        ;

        self::assertFalse((new GenericDomainCollection($collection))->contains(1));
    }

    public function testLazyContainsKey(): void
    {
        self::assertFalse(self::createLazyCollection([])->containsKey(1));
        self::assertTrue(($collection = self::createLazyCollection([1, 'k' => 'v', '2' => null], $visited))->containsKey(2));
        self::assertSame([1, 'k' => 'v', '2' => null], $visited);

        $this->assertUnrewindableGenerator();

        $collection->containsKey(0);
    }

    public function testDecoratedContainsKey(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('containsKey')
            ->with(1)
            ->willReturn(false)
        ;

        self::assertFalse((new GenericDomainCollection($collection))->containsKey(1));
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

    public function testDecoratedFirst(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('first')
            ->willReturn(null)
        ;

        self::assertNull((new GenericDomainCollection($collection))->first());
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

    public function testDecoratedLast(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('last')
            ->willReturn(null)
        ;

        self::assertNull((new GenericDomainCollection($collection))->last());
    }

    public function testLazyGet(): void
    {
        self::assertSame(1, ($collection = self::createLazyCollection([1, 2], $visited))->get(0));
        self::assertSame([1], $visited);
        self::assertSame(1, $collection->get('0'));
        self::assertSame(2, $collection->get(1));

        $this->assertUnrewindableGenerator();

        $collection->get(1);
    }

    public function testLazyGetWithEmptyCollection(): void
    {
        $collection = self::createLazyCollection([]);

        $this->expectException(UnknownCollectionElement::class);

        $collection->get('foo');
    }

    public function testLazyGetWithUnknownKey(): void
    {
        $collection = self::createLazyCollection(['bar' => 'foo', 1]);

        $this->expectException(UnknownCollectionElement::class);

        $collection->get('foo');
    }

    public function testDecoratedGet(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('get')
            ->with(1)
            ->willReturn(null)
        ;

        self::assertNull((new GenericDomainCollection($collection))->get(1));
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

    public function testDecoratedFilter(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('filter')
            ->with($filter = static function (): bool {
                return true;
            })
            ->willReturn($filtered = new GenericDomainCollection([]))
        ;

        self::assertSame($filtered, (new GenericDomainCollection($collection))->filter($filter));
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

    public function testDecoratedSlice(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('slice')
            ->with(0, 1)
            ->willReturn($sliced = new GenericDomainCollection([]))
        ;

        self::assertSame($sliced, (new GenericDomainCollection($collection))->slice(0, 1));
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

    public function testDecoratedMap(): void
    {
        $collection = $this->createMock(DomainCollection::class);
        $collection->expects(self::once())
            ->method('map')
            ->with($mapper = static function (): int {
                return 1;
            })
            ->willReturn($mapped = new GenericDomainCollection([]))
        ;

        self::assertSame($mapped, (new GenericDomainCollection($collection))->map($mapper));
    }

    public function testLazyCount(): void
    {
        self::assertCount(0, self::createLazyCollection([]));
        self::assertCount(2, $collection = self::createLazyCollection([1, 2], $visited));
        self::assertSame([1, 2], $visited);

        $this->assertClosedGenerator();

        \count($collection);
    }

    public function testDecoratedCount(): void
    {
        $countable = $this->createMock(CountableIterator::class);
        $countable->expects(self::once())
            ->method('count')
            ->willReturn(0)
        ;

        self::assertCount(0, new GenericDomainCollection($countable));
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
