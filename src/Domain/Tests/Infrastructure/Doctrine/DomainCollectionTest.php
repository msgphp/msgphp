<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MsgPhp\Domain\DomainCollection as BaseDomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainCollection;
use MsgPhp\Domain\Tests\DomainCollectionTestCase;

final class DomainCollectionTest extends DomainCollectionTestCase
{
    public function testFromValueWithCollection(): void
    {
        self::assertEquals(new DomainCollection($collection = $this->createMock(Collection::class)), DomainCollection::fromValue($collection));
    }

    protected static function createCollection(array $elements): BaseDomainCollection
    {
        return new DomainCollection(new ArrayCollection($elements));
    }
}
