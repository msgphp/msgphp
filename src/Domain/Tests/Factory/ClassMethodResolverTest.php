<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\Exception\InvalidClass;
use MsgPhp\Domain\Factory\ClassMethodResolver;
use PHPUnit\Framework\TestCase;

final class ClassMethodResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $arguments = ClassMethodResolver::resolve(TestClass::class, '__construct');

        self::assertSame([
            'fooBar' => ['index' => 0, 'required' => true, 'default' => null, 'type' => 'string'],
            'foo_bar' => ['index' => 1, 'required' => false, 'default' => null, 'type' => TestClassWrongCase::class],
            'fooBar_Baz' => ['index' => 2, 'required' => false, 'default' => null, 'type' => 'mixed'],
            'it' => ['index' => 3, 'required' => true, 'default' => [], 'type' => 'iterable'],
            '_stdClass' => ['index' => 4, 'required' => true, 'default' => null, 'type' => \stdClass::class],
            'foo' => ['index' => 5, 'required' => false, 'default' => 1, 'type' => 'int'],
            'bar' => ['index' => 6, 'required' => false, 'default' => null, 'type' => TestClass::class],
            'baz' => ['index' => 7, 'required' => false, 'default' => [1], 'type' => 'array'],
        ], $arguments);
    }

    public function testResolveWithoutConstructor(): void
    {
        $object = new class() {
        };
        $arguments = ClassMethodResolver::resolve(\get_class($object), '__construct');

        self::assertSame([], $arguments);
    }

    public function testResolveWithUnknownClass(): void
    {
        $this->expectException(InvalidClass::class);

        /** @psalm-suppress UndefinedClass */
        ClassMethodResolver::resolve(TestUnknownObject::class, 'bar');
    }

    public function testResolveWithUnknownMethod(): void
    {
        $this->expectException(InvalidClass::class);

        ClassMethodResolver::resolve(\stdClass::class, 'bar');
    }
}

class TestClass
{
    /**
     * @psalm-suppress InvalidClass
     *
     * @param mixed $fooBar_Baz
     */
    public function __construct(string $fooBar, ?testclasswrongcase $foo_bar, $fooBar_Baz, iterable $it, \stdClass $_stdClass, int $foo = 1, self $bar = null, array $baz = [1])
    {
    }
}

class TestClassWrongCase
{
}
