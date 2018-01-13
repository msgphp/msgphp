<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\DomainCollection;

final class DomainCollectionTest extends AbstractDomainCollectionTest
{
    public function provideEmptyCollections(): iterable
    {
        foreach (self::getEmptyValues() as $value) {
            if (null !== $value) {
                yield [new DomainCollection($value), $value];
            }

            yield [DomainCollection::fromValue($value), $value];
        }
    }

    public function provideNonEmptyCollections(): iterable
    {
        foreach (self::getNonEmptyValues() as $value) {
            yield [new DomainCollection($value), $value];
            yield [DomainCollection::fromValue($value), $value];
        }

        yield [new DomainCollection((function () {
            yield null;
        })()), [null]];
        yield [DomainCollection::fromValue((function () {
            yield null;
        })()), [null]];
    }
}
