<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests;

use MsgPhp\Domain\{DomainId, DomainIdInterface};

final class DomainIdTest extends AbstractDomainIdTest
{
    public function provideEmptyIds(): iterable
    {
        yield [new DomainId()];
        yield [new DomainId(null)];
        yield [DomainId::fromValue(null)];
    }

    public function provideNonEmptyIds(): iterable
    {
        yield [new DomainId(''), ''];
        yield [new DomainId(' '), ' '];
        yield [new DomainId('0'), '0'];
        yield [new DomainId('foo'), 'foo'];
        yield [DomainId::fromValue(1), '1'];
        yield [DomainId::fromValue(new class() {
            public function __toString(): string
            {
                return 'string';
            }
        }), 'string'];
    }

    protected static function duplicateDomainId(DomainIdInterface $id, bool $otherType = false): DomainIdInterface
    {
        $class = $otherType ? OtherTestDomainId::class : DomainId::class;

        return $id->isEmpty() ? new $class() : new $class($id->toString());
    }
}

class OtherTestDomainId extends DomainId
{
}
