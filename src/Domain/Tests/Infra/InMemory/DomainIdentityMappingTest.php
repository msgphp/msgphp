<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Infra\InMemory\DomainIdentityMapping;
use PHPUnit\Framework\TestCase;

final class DomainIdentityMappingTest extends TestCase
{
    public function testGetIdentifierFieldNames(): void
    {
        $map = new DomainIdentityMapping(['foo' => 'a', 'bar' => ['b'], 'baz' => ['c', 'd']]);

        $this->assertSame(['a'], $map->getIdentifierFieldNames('foo'));
        $this->assertSame(['b'], $map->getIdentifierFieldNames('bar'));
        $this->assertSame(['c', 'd'], $map->getIdentifierFieldNames('baz'));
    }

    public function testGetIdentifierFieldNamesWithInvalidClass(): void
    {
        $map = new DomainIdentityMapping([]);

        $this->expectException(InvalidClassException::class);

        $map->getIdentifierFieldNames('foo');
    }

    public function testGetIdentity(): void
    {
        $map = new DomainIdentityMapping([\stdClass::class => ['b', 'c']]);
        $object1 = new \stdClass();
        $object1->a = 1;
        $object1->b = 2;
        $object1->c = 3;
        $object2 = new \stdClass();
        $object2->b = 2;
        $object2->c = null;

        $this->assertSame(['b' => 2, 'c' => 3], $map->getIdentity($object1));
        $this->assertSame(['b' => 2], $map->getIdentity($object2));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $map = new DomainIdentityMapping([]);

        $this->expectException(InvalidClassException::class);

        $map->getIdentity(new \stdClass());
    }
}
