<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Exception\InvalidEntityClassException;
use MsgPhp\Domain\Infra\InMemory\DomainIdentityMap;
use PHPUnit\Framework\TestCase;

final class DomainIdentityMapTest extends TestCase
{
    public function testGetIdentifierFieldNames(): void
    {
        $map = new DomainIdentityMap(['foo' => 'a', 'bar' => ['b'], 'baz' => ['c', 'd']]);

        $this->assertSame(['a'], $map->getIdentifierFieldNames('foo'));
        $this->assertSame(['b'], $map->getIdentifierFieldNames('bar'));
        $this->assertSame(['c', 'd'], $map->getIdentifierFieldNames('baz'));
    }

    public function testGetIdentifierFieldNamesWithInvalidEntityClass(): void
    {
        $map = new DomainIdentityMap([]);

        $this->expectException(InvalidEntityClassException::class);

        $map->getIdentifierFieldNames('foo');
    }

    public function testGetIdentifierValues(): void
    {
        $map = new DomainIdentityMap([\stdClass::class => ['b', 'c']]);
        $object = new \stdClass();
        $object->a = 1;
        $object->b = 2;
        $object->c = 3;

        $this->assertSame([2, 3], $map->getIdentifierValues($object));
    }

    public function testGetIdentifierValuesWithInvalidEntityClass(): void
    {
        $map = new DomainIdentityMap([]);

        $this->expectException(InvalidEntityClassException::class);

        $map->getIdentifierValues(new \stdClass());
    }
}
