<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity;

use MsgPhp\Domain\Entity\{ChainEntityFactory, EntityFactoryInterface};
use MsgPhp\Domain\Exception\UnknownEntityException;
use MsgPhp\Domain\Infra\InMemory\DomainId;
use PHPUnit\Framework\TestCase;

final class ChainEntityFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory1 = $this->getMockBuilder(EntityFactoryInterface::class)->getMock();
        $factory1->expects($this->any())
            ->method('create')
            ->willThrowException(UnknownEntityException::create('some'));
        $factory2 = $this->getMockBuilder(EntityFactoryInterface::class)->getMock();
        $factory2->expects($this->any())
            ->method('create')
            ->willReturn($entity = new \stdClass());
        $factory = new ChainEntityFactory([$factory1, $factory2]);

        $this->assertSame($entity, $factory->create('some'));
    }

    public function testCreateWithoutFactories(): void
    {
        $factory = new ChainEntityFactory([]);

        $this->expectException(UnknownEntityException::class);

        $factory->create('some');
    }

    public function testIdentify(): void
    {
        $factory1 = $this->getMockBuilder(EntityFactoryInterface::class)->getMock();
        $factory1->expects($this->any())
            ->method('identify')
            ->willThrowException(UnknownEntityException::create('some'));
        $factory2 = $this->getMockBuilder(EntityFactoryInterface::class)->getMock();
        $factory2->expects($this->any())
            ->method('identify')
            ->willReturnCallback(function ($entity, $id) { return new DomainId($id); });
        $factory = new ChainEntityFactory([$factory1, $factory2]);

        $this->assertSame('id', $factory->identify('some', 'id')->toString());
    }

    public function testIdentifyWithoutFactories(): void
    {
        $factory = new ChainEntityFactory([]);

        $this->expectException(UnknownEntityException::class);

        $factory->identify('some', 'id');
    }
}
