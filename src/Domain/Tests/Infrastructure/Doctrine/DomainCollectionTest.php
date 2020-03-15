<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use MsgPhp\Domain\DomainCollection as BaseDomainCollection;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainCollection;
use MsgPhp\Domain\Tests\DomainCollectionTestCase;

/**
 * @internal
 */
final class DomainCollectionTest extends DomainCollectionTestCase
{
    protected static function createCollection(array $elements): BaseDomainCollection
    {
        return new DomainCollection(new ArrayCollection($elements));
    }
}
