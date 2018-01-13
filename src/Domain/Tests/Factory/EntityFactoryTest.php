<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\DomainIdInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\{DomainObjectFactoryInterface, EntityFactory};
use PHPUnit\Framework\TestCase;

final class EntityFactoryTest extends TestCase
{
    /** @var EntityFactory */
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

        $this->factory = new EntityFactory(['alias_id' => 'id'], $innerFactory);
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

        $this->expectException(InvalidClassException::class);

        $this->factory->identify('foo', '1');
    }

    public function testNextIdentity(): void
    {
        $this->assertSame('new', $this->factory->nextIdentity('id')->toString());
        $this->assertSame('new', $this->factory->nextIdentity('alias_id')->toString());

        $this->expectException(InvalidClassException::class);

        $this->factory->nextIdentity('foo');
    }
}
