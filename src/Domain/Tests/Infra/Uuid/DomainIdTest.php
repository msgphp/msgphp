<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Uuid;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Infra\Uuid\DomainId;
use MsgPhp\Domain\Tests\AbstractDomainIdTest;
use Ramsey\Uuid\Uuid;

final class DomainIdTest extends AbstractDomainIdTest
{
    public function testRandomUuid(): void
    {
        $this->assertNotSame((new DomainId())->toString(), (new DomainId())->toString());
    }

    public function provideEmptyIds(): iterable
    {
        return new \EmptyIterator();
    }

    public function provideNonEmptyIds(): iterable
    {
        $uuid = '00000000-0000-0000-0000-000000000000';

        yield [$id = new DomainId(), $id->toString()];
        yield [new DomainId(Uuid::fromString($uuid)), $uuid];
        yield [new DomainId($value = Uuid::fromString($uuid)), $value];
        yield [DomainId::fromValue(new class() {
            public function __toString(): string
            {
                return '00000000-0000-0000-0000-000000000000';
            }
        }), $uuid];
    }

    protected static function duplicateDomainId(DomainIdInterface $id, bool $otherType = false): DomainIdInterface
    {
        $class = $otherType ? OtherTestDomainId::class : DomainId::class;

        return $id->isEmpty() ? new $class() : new $class(Uuid::fromString($id->toString()));
    }
}

class OtherTestDomainId extends DomainId
{
}
