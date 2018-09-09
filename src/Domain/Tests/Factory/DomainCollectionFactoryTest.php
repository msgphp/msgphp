<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\{DomainCollection, DomainCollectionInterface};
use MsgPhp\Domain\Factory\DomainCollectionFactory;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection as DoctrineDomainCollection;
use PHPUnit\Framework\TestCase;

final class DomainCollectionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        self::assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create(null));
        self::assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create([]));
        self::assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create([1, 2, 3]));
        self::assertInstanceOf(DoctrineDomainCollection::class, DomainCollectionFactory::create($this->createMock(Collection::class)));
        self::assertSame($collection = $this->createMock(DomainCollectionInterface::class), DomainCollectionFactory::create($collection));
    }

    public function testCreateFromCallable(): void
    {
        $visited = false;
        $collection = DomainCollectionFactory::createFromCallable(function () use (&$visited): iterable {
            $visited = false;
            yield 1;
            yield 2;
            $visited = true;
        });

        self::assertFalse($collection->isEmpty());
        self::assertFalse($visited);
        self::assertSame([1, 2], iterator_to_array($collection));
        self::assertTrue($visited);
        self::assertSame(1, $collection->first());
        self::assertFalse($visited);
    }
}
