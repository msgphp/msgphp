<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Infra\InMemory\ObjectIdentityMap;
use PHPUnit\Framework\TestCase;

final class ObjectIdentityMapTest extends TestCase
{
    public function testGetGlobalDefault(): void
    {
        self::assertSame(ObjectIdentityMap::getGlobalDefault(), ObjectIdentityMap::getGlobalDefault());
    }

    public function testAll(): void
    {
        $identityMap = new ObjectIdentityMap();
        $objects = [];
        $anonymous = new class() {
        };

        foreach ([$std1 = new \stdClass(), $std2 = new \stdClass(), $anonymous] as $object) {
            $identityMap->persist($object);
            $objects[\get_class($object)] = [];
        }
        $identityMap->persist($std1);

        foreach (array_keys($objects) as $class) {
            foreach ($identityMap->all($class) as $i => $object) {
                $objects[$class][$i] = $object;
            }
        }

        self::assertSame([$std1, $std2], $objects[\stdClass::class]);
        self::assertSame([$anonymous], $objects[\get_class($anonymous)]);

        $identityMap->remove($anonymous);

        $class = \get_class($anonymous);
        $objects[$class] = [];

        foreach ($identityMap->all($class) as $i => $object) {
            $objects[$class][$i] = $object;
        }

        self::assertSame([], $objects[$class]);
    }

    public function testContains(): void
    {
        $identityMap = new ObjectIdentityMap();

        self::assertFalse($identityMap->contains($object = new \stdClass()));

        $identityMap->persist($object);

        self::assertTrue($identityMap->contains($object));

        $identityMap->remove($object);

        self::assertFalse($identityMap->contains($object));
    }
}
