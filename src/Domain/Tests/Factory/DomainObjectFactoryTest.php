<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\{DomainCollection, DomainId};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\{DomainObjectFactory, DomainObjectFactoryInterface};
use PHPUnit\Framework\TestCase;

final class DomainObjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $object = (new DomainObjectFactory())->create(TestObject::class, ['argB' => 'foo', 'arg_a' => 'ignore', 0 => 'ignore', 'argA' => 1]);

        self::assertInstanceOf(TestObject::class, $object);
        self::assertSame(1, $object->a);
        self::assertSame('foo', $object->b);
    }

    public function testCreateDefaultsConstructorArguments(): void
    {
        $object = (new DomainObjectFactory())->create(TestObject::class);

        self::assertNull($object->a);
        self::assertSame('default-b', $object->b);
    }

    public function testCreatePreservesContext(): void
    {
        $object = (new DomainObjectFactory())->create(TestObject::class, ['argB' => null]);

        self::assertInstanceOf(TestObject::class, $object);
        self::assertNull($object->b);
    }

    public function testCreateWithAlias(): void
    {
        $object = (new DomainObjectFactory(['alias' => \stdClass::class]))->create('alias');

        self::assertInstanceOf(\stdClass::class, $object);
    }

    public function testCreateWithDomainId(): void
    {
        self::assertInstanceOf(DomainId::class, (new DomainObjectFactory())->create(DomainId::class, [1]));
    }

    public function testCreateWithDomainCollection(): void
    {
        self::assertInstanceOf(DomainCollection::class, (new DomainObjectFactory())->create(DomainCollection::class, [null]));
    }

    public function testCreateWithUnknownObject(): void
    {
        $factory = new DomainObjectFactory();

        $this->expectException(InvalidClassException::class);

        $factory->create(UnknownTestObject::class);
    }

    public function testCreateWithUnknownNestedObject(): void
    {
        $factory = new DomainObjectFactory();

        self::assertInstanceOf(KnownTestObject::class, $factory->create(KnownTestObject::class));
        self::assertInstanceOf(KnownTestObject::class, $factory->create(KnownTestObject::class, ['unknown' => 'foo']));

        $this->expectException(\TypeError::class);

        $factory->create(KnownTestObject::class, ['arg' => []]);
    }

    public function testCreateWithoutConstructor(): void
    {
        self::assertInstanceOf(EmptyTestObject::class, (new DomainObjectFactory())->create(EmptyTestObject::class, ['arg']));
    }

    public function testCreateWithPrivateConstructor(): void
    {
        $factory = new DomainObjectFactory();

        $this->expectException(\Error::class);

        $factory->create(PrivateTestObject::class, ['arg']);
    }

    public function testNestedCreate(): void
    {
        $object = (new DomainObjectFactory())->create(NestedTestObject::class, [
            'test' => ['argA' => 'nested_a', 'argB' => 'nested_b', 'arg_b' => 'ignore'],
            'self' => ['test' => ['argA' => 'foo', 'argB' => 'bar'], 'other' => $other = new TestObject(1, 2)],
        ]);

        self::assertInstanceOf(NestedTestObject::class, $object);
        self::assertInstanceOf(TestObject::class, $object->test);
        self::assertSame('nested_a', $object->test->a);
        self::assertSame('nested_b', $object->test->b);
        self::assertInstanceOf(NestedTestObject::class, $object->self);
        self::assertSame('foo', $object->self->test->a);
        self::assertSame('bar', $object->self->test->b);
        self::assertSame($other, $object->self->other);
    }

    public function testNestedCreateWithoutRequiredContext(): void
    {
        $factory = new DomainObjectFactory();

        $this->expectException(\LogicException::class);

        $factory->create(NestedTestObject::class);
    }

    public function testNestedCreateWithNestedFactory(): void
    {
        $nestedFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $nestedFactory->expects(self::once())
            ->method('create')
            ->willReturn($nested = new TestObject(1, 2))
        ;

        $factory = new DomainObjectFactory();
        $factory->setNestedFactory($nestedFactory);

        self::assertInstanceOf(NestedTestObject::class, $object = $factory->create(NestedTestObject::class, ['test' => null]));
        self::assertSame($nested, $object->test);
    }

    public function testNestedCreateWithInvalidNestedFactory(): void
    {
        $nestedFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $nestedFactory->expects(self::once())
            ->method('create')
            ->willThrowException(new \RuntimeException())
        ;

        $factory = new DomainObjectFactory();
        $factory->setNestedFactory($nestedFactory);

        $this->expectException(\RuntimeException::class);

        $factory->create(NestedTestObject::class, ['test' => ['irrelevant']]);
    }

    public function testGetClass(): void
    {
        $factory = new DomainObjectFactory(['alias' => \stdClass::class]);

        self::assertSame('foo', $factory->getClass('foo'));
        self::assertSame(\stdClass::class, $factory->getClass('alias'));
        self::assertSame(\stdClass::class, $factory->getClass(\stdClass::class));
    }
}

class TestObject
{
    public $a = 'default-a';
    public $b;

    public function __construct($argA, $argB = 'default-b')
    {
        $this->a = $argA;
        $this->b = $argB;
    }
}

class NestedTestObject
{
    public $test;
    public $self;
    public $other;

    public function __construct(TestObject $test, ?self $self, ?TestObject $other)
    {
        $this->test = $test;
        $this->self = $self;
        $this->other = $other;
    }
}

class EmptyTestObject
{
}

class PrivateTestObject
{
    private function __construct()
    {
    }
}

class KnownTestObject
{
    public function __construct(UnknownTestObject $arg = null)
    {
    }
}
