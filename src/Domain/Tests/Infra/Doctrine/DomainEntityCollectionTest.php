<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection;
use MsgPhp\Domain\Tests\AbstractDomainCollectionTest;

final class DomainEntityCollectionTest extends AbstractDomainCollectionTest
{
    protected static function createCollection(array $elements): DomainCollectionInterface
    {
        return new DomainCollection(new ArrayCollection($elements));
    }
}
