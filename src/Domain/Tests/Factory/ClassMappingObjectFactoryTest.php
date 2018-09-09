<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\Factory\{ClassMappingObjectFactory, DomainObjectFactoryInterface};
use PHPUnit\Framework\TestCase;

final class ClassMappingObjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::any())
            ->method('create')
            ->willReturnCallback(function ($class, $context) {
                $o = new \stdClass();
                $o->class = $class;
                $o->context = $context;

                return $o;
            });
        $factory = new ClassMappingObjectFactory($innerFactory, ['foo' => 'bar']);

        self::assertSame(['class' => 'bar', 'context' => []], (array) $factory->create('foo'));
        self::assertSame(['class' => 'Foo', 'context' => ['context']], (array) $factory->create('Foo', ['context']));
    }
}
