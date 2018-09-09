<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\InMemory;

use MsgPhp\Domain\Infra\InMemory\ObjectFieldAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ObjectFieldAccessorTest extends TestCase
{
    /**
     * @dataProvider provideAccessor
     */
    public function testGetValue(ObjectFieldAccessor $accessor): void
    {
        $object = new \stdClass();
        $object->field = null;
        $object->field2 = 'foo';
        $object2 = new class() {
            public function getFieldA()
            {
                return 1;
            }

            public function hasFieldB()
            {
                return true;
            }

            public function isFieldC()
            {
                return false;
            }

            public function otherField()
            {
                return 'bar';
            }
        };

        self::assertNull($accessor->getValue($object, 'field'));
        self::assertSame('foo', $accessor->getValue($object, 'field2'));
        self::assertSame(1, $accessor->getValue($object2, 'fieldA'));
        self::assertTrue($accessor->getValue($object2, 'fieldB'));
        self::assertFalse($accessor->getValue($object2, 'fieldC'));
        self::assertSame('bar', $accessor->getValue($object2, 'otherField'));
        self::assertSame(1, $accessor->getValue($object2, 'getFieldA'));
    }

    /**
     * @dataProvider provideAccessor
     */
    public function testGetValueWithInvalidField(ObjectFieldAccessor $accessor): void
    {
        $this->expectException(\RuntimeException::class);

        $accessor->getValue(new \stdClass(), 'foo');
    }

    public function provideAccessor(): iterable
    {
        yield [new ObjectFieldAccessor()];
        yield [new ObjectFieldAccessor(new PropertyAccessor())];
    }
}
