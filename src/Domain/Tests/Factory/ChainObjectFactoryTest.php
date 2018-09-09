<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\{ChainObjectFactory, DomainObjectFactoryInterface};
use PHPUnit\Framework\TestCase;

final class ChainObjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory1 = $this->createMock(DomainObjectFactoryInterface::class);
        $factory1->expects(self::any())
            ->method('create')
            ->willThrowException(InvalidClassException::create('some'));
        $factory2 = $this->createMock(DomainObjectFactoryInterface::class);
        $factory2->expects(self::any())
            ->method('create')
            ->willReturn($object = new \stdClass());

        self::assertSame($object, (new ChainObjectFactory([$factory1, $factory2]))->create('some'));
    }

    public function testCreateWithoutFactories(): void
    {
        $factory = new ChainObjectFactory([]);

        $this->expectException(InvalidClassException::class);

        $factory->create('some');
    }
}
