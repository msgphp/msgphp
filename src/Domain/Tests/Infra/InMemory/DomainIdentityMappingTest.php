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
        $mapping = new DomainIdentityMapping(['foo' => 'a', 'bar' => ['b'], 'baz' => ['c', 'd']]);

        $this->assertSame(['a'], $mapping->getIdentifierFieldNames('foo'));
        $this->assertSame(['b'], $mapping->getIdentifierFieldNames('bar'));
        $this->assertSame(['c', 'd'], $mapping->getIdentifierFieldNames('baz'));
    }

    public function testGetIdentifierFieldNamesWithInvalidClass(): void
    {
        $mapping = new DomainIdentityMapping([]);

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentifierFieldNames('foo');
    }

    public function testGetIdentity(): void
    {
        $mapping = new DomainIdentityMapping([\stdClass::class => ['b', 'c']]);
        $object1 = new \stdClass();
        $object1->a = 1;
        $object1->b = 2;
        $object1->c = 3;
        $object2 = new \stdClass();
        $object2->b = 2;
        $object2->c = null;
        $object3 = new \stdClass();
        $object3->a = 'foo';
        $object3->b = null;
        $object3->c = null;

        $this->assertSame(['b' => 2, 'c' => 3], $mapping->getIdentity($object1));
        $this->assertSame(['b' => 2], $mapping->getIdentity($object2));
        $this->assertSame([], $mapping->getIdentity($object3));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $mapping = new DomainIdentityMapping([]);

        $this->expectException(InvalidClassException::class);

        $mapping->getIdentity(new \stdClass());
    }
}
