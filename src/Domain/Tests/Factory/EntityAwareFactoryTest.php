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

        $this->factory = new EntityAwareFactory(['alias_id' => 'id'], $innerFactory);
    }

    public function testCreate(): void
    {
        $this->assertSame(['class' => 'foo', 'context' => []], (array) $this->factory->create('foo'));
        $this->assertSame(['class' => 'bar', 'context' => ['context']], (array) $this->factory->create('bar', ['context']));
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
