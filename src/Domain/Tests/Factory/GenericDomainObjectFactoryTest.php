<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\Exception\InvalidClass;
use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\Domain\Factory\GenericDomainObjectFactory;
use MsgPhp\Domain\GenericDomainCollection;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GenericDomainObjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $object = (new GenericDomainObjectFactory())->create(TestObject::class, ['argB' => 'foo', 'arg_a' => 'ignore', 0 => 'ignore', 'argA' => 1]);

        self::assertSame(1, $object->a);
        self::assertSame('foo', $object->b);
    }

    public function testCreateDefaultsConstructorArguments(): void
    {
        $object = (new GenericDomainObjectFactory())->create(TestObject::class);

        self::assertNull($object->a);
        self::assertSame('default-b', $object->b);
    }

    public function testCreatePreservesContext(): void
    {
        $object = (new GenericDomainObjectFactory())->create(TestObject::class, ['argB' => null]);

        self::assertNull($object->b);
    }

    public function testCreateWithAlias(): void
    {
        $object = (new GenericDomainObjectFactory([
            TestEmptyObject::class => TestExtendedEmptyObject::class,
        ]))->create(TestEmptyObject::class);

        self::assertInstanceOf(TestExtendedEmptyObject::class, $object);
    }

    public function testCreateWithDomainId(): void
    {
        $id = (new GenericDomainObjectFactory())->create(TestDomainId::class, ['value' => 123]);

        self::assertSame('123', $id->toString());
    }

    public function testCreateWithDomainCollection(): void
    {
        $collection = (new GenericDomainObjectFactory())->create(GenericDomainCollection::class, ['elements' => [1, 2, 3]]);

        self::assertSame([1, 2, 3], iterator_to_array($collection));
    }

    public function testCreateWithUnknownObject(): void
    {
        $factory = new GenericDomainObjectFactory();

        $this->expectException(InvalidClass::class);

        /** @psalm-suppress UndefinedClass */
        $factory->create(TestUnknownObject::class);
    }

    public function testCreateWithUnknownNestedObject(): void
    {
        $factory = new GenericDomainObjectFactory();

        self::assertInstanceOf(TestKnownObject::class, $factory->create(TestKnownObject::class));
        self::assertInstanceOf(TestKnownObject::class, $factory->create(TestKnownObject::class, ['unknown' => 'foo']));

        $this->expectException(\TypeError::class);

        $factory->create(TestKnownObject::class, ['arg' => []]);
    }

    public function testCreateWithoutConstructor(): void
    {
        self::assertInstanceOf(TestEmptyObject::class, (new GenericDomainObjectFactory())->create(TestEmptyObject::class, ['arg']));
    }

    public function testCreateWithPrivateConstructor(): void
    {
        $factory = new GenericDomainObjectFactory();

        $this->expectException(\Error::class);

        $factory->create(TestPrivateObject::class, ['arg']);
    }

    public function testNestedCreate(): void
    {
        $object = (new GenericDomainObjectFactory())->create(TestNestedObject::class, [
            'test' => ['argA' => 'nested_a', 'argB' => 'nested_b', 'arg_b' => 'ignore'],
            'self' => ['test' => ['argA' => 'foo', 'argB' => 'bar'], 'other' => $other = new TestObject(1, 2)],
        ]);

        self::assertSame('nested_a', $object->test->a);
        self::assertSame('nested_b', $object->test->b);
        self::assertInstanceOf(TestNestedObject::class, $object->self);
        self::assertSame('foo', $object->self->test->a);
        self::assertSame('bar', $object->self->test->b);
        self::assertSame($other, $object->self->other);
    }

    public function testNestedCreateWithoutRequiredContext(): void
    {
        $factory = new GenericDomainObjectFactory();

        $this->expectException(\LogicException::class);

        $factory->create(TestNestedObject::class);
    }

    public function testNestedCreateWithNestedFactory(): void
    {
        $nestedFactory = $this->createMock(DomainObjectFactory::class);
        $nestedFactory->expects(self::once())
            ->method('create')
            ->willReturn($nested = new TestObject(1, 2))
        ;

        $factory = new GenericDomainObjectFactory();
        $factory->setNestedFactory($nestedFactory);
        $object = $factory->create(TestNestedObject::class, ['test' => null]);

        self::assertSame($nested, $object->test);
    }

    public function testNestedCreateWithInvalidNestedFactory(): void
    {
        $nestedFactory = $this->createMock(DomainObjectFactory::class);
        $nestedFactory->expects(self::once())
            ->method('create')
            ->willThrowException(new \RuntimeException())
        ;

        $factory = new GenericDomainObjectFactory();
        $factory->setNestedFactory($nestedFactory);

        $this->expectException(\RuntimeException::class);

        $factory->create(TestNestedObject::class, ['test' => ['irrelevant']]);
    }

    public function testReference(): void
    {
        $reference = (new GenericDomainObjectFactory())->reference(TestReferenceObject::class, ['fieldA' => 1, 'fieldB' => 2]);

        self::assertSame([1, 2], $reference->get());
    }

    public function testReferenceWithUnknownClass(): void
    {
        $factory = new GenericDomainObjectFactory();

        $this->expectException(InvalidClass::class);

        /** @psalm-suppress UndefinedClass */
        $factory->reference(TestUnknownObject::class);
    }

    public function testGetClass(): void
    {
        $factory = new GenericDomainObjectFactory([
            TestEmptyObject::class => TestExtendedEmptyObject::class,
        ]);

        self::assertSame(TestExtendedEmptyObject::class, $factory->getClass(TestEmptyObject::class));
        self::assertSame(\stdClass::class, $factory->getClass(\stdClass::class));
    }
}

class TestObject
{
    /** @var mixed */
    public $a = 'default-a';
    /** @var mixed */
    public $b;

    /**
     * @param mixed $argA
     * @param mixed $argB
     */
    public function __construct($argA, $argB = 'default-b')
    {
        $this->a = $argA;
        $this->b = $argB;
    }
}

class TestNestedObject
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

class TestEmptyObject
{
}

class TestExtendedEmptyObject extends TestEmptyObject
{
}

class TestPrivateObject
{
    private function __construct()
    {
    }
}

class TestKnownObject
{
    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(?TestUnknownObject $arg = null)
    {
    }
}

class TestReferenceObject
{
    /** @var mixed */
    private $fieldA;
    /** @var mixed */
    private $fieldB = 'default B';

    public function __construct()
    {
        throw new \BadMethodCallException();
    }

    /**
     * @return array{0:mixed,1:mixed}
     */
    public function get(): array
    {
        return [$this->fieldA, $this->fieldB];
    }
}
