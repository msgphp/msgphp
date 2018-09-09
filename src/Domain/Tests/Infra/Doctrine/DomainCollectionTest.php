<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection;
use MsgPhp\Domain\Tests\AbstractDomainCollectionTest;

final class DomainCollectionTest extends AbstractDomainCollectionTest
{
    public function testFromValueWithCollection(): void
    {
        self::assertEquals(new DomainCollection($collection = $this->createMock(Collection::class)), DomainCollection::fromValue($collection));
    }

    protected static function createCollection(array $elements): DomainCollectionInterface
    {
        return new DomainCollection(new ArrayCollection($elements));
    }
}
