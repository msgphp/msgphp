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
        $this->assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create(null));
        $this->assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create([]));
        $this->assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create($this->createMock(DomainCollectionInterface::class)));
        $this->assertInstanceOf(DomainCollection::class, DomainCollectionFactory::create([1, 2, 3]));
        $this->assertInstanceOf(DoctrineDomainCollection::class, DomainCollectionFactory::create($this->createMock(Collection::class)));
    }
}
