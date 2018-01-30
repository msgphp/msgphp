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
        $object = new class('foo', null, null) {
            public function __construct(string $fooBar, ?wrongcase $foo_bar, $fooBar_Baz, int $foo = 1, SELF $bar = null)
            {
                $fooBar;
                $foo_bar;
                $fooBar_Baz;
                $foo;
                $bar;
            }
        };
        $arguments = ClassMethodResolver::resolve($class = get_class($object), '__construct');

        $this->assertSame([
            ['name' => 'fooBar', 'required' => true, 'default' => null, 'type' => 'string'],
            ['name' => 'foo_bar', 'required' => false, 'default' => null, 'type' => WrongCase::class],
            ['name' => 'fooBar_Baz', 'required' => false, 'default' => null, 'type' => null],
            ['name' => 'foo', 'required' => false, 'default' => 1, 'type' => 'int'],
            ['name' => 'bar', 'required' => false, 'default' => null, 'type' => get_class($object)],
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

class WrongCase
{
}
