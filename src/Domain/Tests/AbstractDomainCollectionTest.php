<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollectionInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDomainCollectionTest extends TestCase
{
    /**
     * @dataProvider provideEmptyCollections
     */
    public function testEmptyCollection(DomainCollectionInterface $collection): void
    {
        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->contains(1));
        $this->assertFalse($collection->contains('1'));
        $this->assertFalse($collection->first());
        $this->assertFalse($collection->last());
        $this->assertSame([], iterator_to_array($collection->filter(function (): bool {
            return true;
        })));
        $this->assertSame([], iterator_to_array($collection->slice(10)));
        $this->assertSame([], iterator_to_array($collection->slice(0, 10)));
        $this->assertSame([], iterator_to_array($collection->slice(1, 1)));
        $this->assertSame([], iterator_to_array($collection->slice(10, 10)));
        $this->assertSame([], $collection->map(function () {
            return null;
        }));
        $this->assertCount(0, $collection);
        $this->assertSame([], iterator_to_array($collection));
    }

    /**
     * @dataProvider provideNonEmptyCollections
     */
    public function testNonEmptyCollection(DomainCollectionInterface $collection, iterable $expected): void
    {
        $expected = $expected instanceof \Traversable ? iterator_to_array($expected) : $expected;
        $count = count($expected);

        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(reset($expected)));
        $this->assertFalse($collection->contains('2'));
        $this->assertSame(reset($expected), $collection->first());
        $this->assertSame(end($expected), $collection->last());
        $this->assertSame(array_slice($expected, 1, null, true), iterator_to_array($collection->filter(function () use (&$i): bool {
            return ++$i > 1;
        })));
        unset($i);
        $this->assertSame([], iterator_to_array($collection->slice($count)));
        $this->assertSame(array_slice($expected, $count - 1, null, true), iterator_to_array($collection->slice($count - 1)));
        $this->assertSame(array_slice($expected, 0, $count - 1 ?: null, true), iterator_to_array($collection->slice(0, $count - 1)));
        $this->assertSame(array_slice($expected, 0, $count, true), iterator_to_array($collection->slice(0, $count)));
        $this->assertSame(array_slice($expected, 1, 1, true), iterator_to_array($collection->slice(1, 1)));
        $this->assertSame([], iterator_to_array($collection->slice(10, 10)));
        $this->assertSame(range(1, $count), $collection->map(function () use (&$i) {
            return ++$i;
        }));
        unset($i);
        $this->assertCount($count, $collection);
        $this->assertSame($expected, iterator_to_array($collection));
    }

    abstract public function provideEmptyCollections(): iterable;

    abstract public function provideNonEmptyCollections(): iterable;

    final protected static function getEmptyValues(): iterable
    {
        yield null;
        yield [];
        yield new \EmptyIterator();
    }

    final protected static function getNonEmptyValues(): iterable
    {
        yield [null];
        yield [''];
        yield [1, 2, 3, '1'];
        yield new \ArrayIterator([true, false]);
    }
}
