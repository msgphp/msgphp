<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Infra\InMemory\GlobalObjectMemory;
use PHPUnit\Framework\TestCase;

final class GlobalObjectMemoryTest extends TestCase
{
    public function testCreateDefault(): void
    {
        $this->assertSame(GlobalObjectMemory::createDefault(), GlobalObjectMemory::createDefault());
    }

    public function testAll(): void
    {
        $memory = new GlobalObjectMemory();
        $objects = [];
        $anonymous = new class() {
        };

        foreach ([$std1 = new \stdClass(), $std2 = new \stdClass(), $anonymous] as $object) {
            $memory->persist($object);
            $objects[\get_class($object)] = [];
        }
        $memory->persist($std1);

        foreach (array_keys($objects) as $class) {
            foreach ($memory->all($class) as $i => $object) {
                $objects[$class][$i] = $object;
            }
        }

        $this->assertSame([$std1, $std2], $objects[\stdClass::class]);
        $this->assertSame([$anonymous], $objects[\get_class($anonymous)]);

        $memory->remove($anonymous);

        $class = \get_class($anonymous);
        $objects[$class] = [];

        foreach ($memory->all($class) as $i => $object) {
            $objects[$class][$i] = $object;
        }

        $this->assertSame([], $objects[$class]);
    }

    public function testContains(): void
    {
        $memory = new GlobalObjectMemory();

        $this->assertFalse($memory->contains($object = new \stdClass()));

        $memory->persist($object);

        $this->assertTrue($memory->contains($object));

        $memory->remove($object);

        $this->assertFalse($memory->contains($object));
    }
}
