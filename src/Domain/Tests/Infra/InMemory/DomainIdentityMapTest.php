<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Exception\InvalidClassException;
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

    public function testGetIdentifierFieldNamesWithInvalidgetIdentityClass(): void
    {
        $map = new DomainIdentityMap([]);

        $this->expectException(InvalidClassException::class);

        $map->getIdentifierFieldNames('foo');
    }

    public function testGetIdentity(): void
    {
        $map = new DomainIdentityMap([\stdClass::class => ['b', 'c']]);
        $object = new \stdClass();
        $object->a = 1;
        $object->b = 2;
        $object->c = 3;

        $this->assertSame(['b' => 2, 'c' => 3], $map->getIdentity($object));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $map = new DomainIdentityMap([]);

        $this->expectException(InvalidClassException::class);

        $map->getIdentity(new \stdClass());
    }
}
