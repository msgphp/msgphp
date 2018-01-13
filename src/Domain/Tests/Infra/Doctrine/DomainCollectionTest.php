<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use MsgPhp\Domain\Infra\Doctrine\DomainCollection;
use MsgPhp\Domain\Tests\AbstractDomainCollectionTest;

final class DomainCollectionTest extends AbstractDomainCollectionTest
{
    public function provideEmptyCollections(): iterable
    {
        foreach (self::getEmptyValues() as $value) {
            if (is_array($value)) {
                yield [new DomainCollection(new ArrayCollection($value)), $value];
            }

            yield [DomainCollection::fromValue($value), $value];
        }
    }

    public function provideNonEmptyCollections(): iterable
    {
        foreach (self::getNonEmptyValues() as $value) {
            if (is_array($value)) {
                yield [new DomainCollection(new ArrayCollection($value)), $value];
            }

            yield [DomainCollection::fromValue($value), $value];
        }

        yield [DomainCollection::fromValue((function () {
            yield null;
        })()), [null]];
    }
}
