<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\{DomainObjectFactoryInterface, EntityAwareFactory};
use PHPUnit\Framework\TestCase;

final class EntityAwareFactoryTest extends TestCase
{
    /** @var EntityAwareFactory */
    private $factory;

    protected function setUp(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($class, $context) {
                if ('id' === $class) {
                    $id = $this->createMock(DomainIdInterface::class);
                    $id->expects($this->any())
                        ->method('toString')
                        ->willReturn($context ? reset($context) : 'new');

                    return $id;
                }

                $o = new \stdClass();
                $o->class = $class;
                $o->context = $context;

                return $o;
            });

        $this->factory = new EntityAwareFactory($innerFactory, ['alias_id' => 'id'], function ($class, $id) {
            $o = new \stdClass();
            $o->class = $class;
            $o->id = $id;

            return $o;
        });
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(\stdClass::class, $object = $this->factory->create('foo'));
        $this->assertSame(['class' => 'foo', 'context' => []], (array) $object);
        $this->assertInstanceOf(\stdClass::class, $object = $this->factory->create('bar', ['context']));
        $this->assertSame(['class' => 'bar', 'context' => ['context']], (array) $object);
    }

    public function testReference(): void
    {
        $this->assertInstanceOf(\stdClass::class, $object = $this->factory->reference('foo', 1));
        $this->assertSame(['class' => 'foo', 'id' => 1], (array) $object);
        $this->assertInstanceOf(\stdClass::class, $object = $this->factory->reference('bar', ['id' => 1, 'foo' => '2']));
        $this->assertSame(['class' => 'bar', 'id' => ['id' => 1, 'foo' => '2']], (array) $object);
    }

    public function testReferenceWithoutLoader(): void
    {
        $factory = new EntityAwareFactory($this->createMock(DomainObjectFactoryInterface::class), []);

        $this->expectException(\LogicException::class);

        $factory->reference('foo', 1);
    }

    public function testReferenceWithoutResult(): void
    {
        $factory = new EntityAwareFactory($this->createMock(DomainObjectFactoryInterface::class), [], function ($class, $id) {
            return null;
        });

        $this->expectException(\RuntimeException::class);

        $factory->reference('foo', 1);
    }

    public function testIdentify(): void
    {
        $this->assertSame('1', $this->factory->identify('id', '1')->toString());
        $this->assertSame('1', $this->factory->identify('alias_id', '1')->toString());
        $this->assertSame($id = $this->createMock(DomainIdInterface::class), $this->factory->identify('id', $id));
        $this->assertSame($id = $this->createMock(DomainIdInterface::class), $this->factory->identify('alias_id', $id));

        $this->expectException(InvalidClassException::class);

        $this->factory->identify('foo', '1');
    }

    public function tesNextIdentifier(): void
    {
        $this->assertSame('new', $this->factory->nextIdentifier('id')->toString());
        $this->assertSame('new', $this->factory->nextIdentifier('alias_id')->toString());

        $this->expectException(InvalidClassException::class);

        $this->factory->nextIdentifier('foo');
    }
}
