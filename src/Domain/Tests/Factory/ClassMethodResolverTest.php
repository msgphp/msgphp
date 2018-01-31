<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\ClassMethodResolver;
use PHPUnit\Framework\TestCase;

final class ClassMethodResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $arguments = ClassMethodResolver::resolve(TestClass::class, '__construct');

        $this->assertSame([
            ['name' => 'fooBar', 'key' => 'foo_bar', 'required' => true, 'default' => null, 'type' => 'string'],
            ['name' => 'foo_bar', 'key' => 'foo_bar', 'required' => false, 'default' => null, 'type' => WrongCase::class],
            ['name' => 'fooBar_Baz', 'key' => 'foo_bar_baz', 'required' => false, 'default' => null, 'type' => null],
            ['name' => 'foo', 'key' => 'foo', 'required' => false, 'default' => 1, 'type' => 'int'],
            ['name' => 'bar', 'key' => 'bar', 'required' => false, 'default' => null, 'type' => TestClass::class],
            ['name' => 'baz', 'key' => 'baz', 'required' => false, 'default' => [1], 'type' => 'array'],
            ['name' => 'qux', 'key' => 'qux', 'required' => true, 'default' => [], 'type' => 'iterable'],
        ], $arguments);
    }

    public function testResolveWithoutConstructor(): void
    {
        $object = new class() {
        };
        $arguments = ClassMethodResolver::resolve(get_class($object), '__construct');

        $this->assertSame([], $arguments);
    }

    public function testResolveWithUnknownClass(): void
    {
        $this->expectException(InvalidClassException::class);

        ClassMethodResolver::resolve('foo', 'bar');
    }

    public function testResolveWithUnknownMethod(): void
    {
        $object = new class('foo', null, null) {
        };

        $this->expectException(InvalidClassException::class);

        ClassMethodResolver::resolve(get_class($object), 'bar');
    }
}

class TestClass
{
    public function __construct(string $fooBar, ?wrongcase $foo_bar, $fooBar_Baz, int $foo = 1, SELF $bar = null, array $baz = [1], iterable $qux)
    {
        $fooBar;
        $foo_bar;
        $fooBar_Baz;
        $foo;
        $bar;
        $baz;
        $qux;
    }
}

class WrongCase
{
}
