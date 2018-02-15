<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\{DomainId, DomainIdInterface};
use MsgPhp\Domain\Factory\DomainIdFactory;
use MsgPhp\Domain\Infra\Uuid\DomainId as DomainUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

final class DomainIdFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $this->assertInstanceOf(DomainId::class, DomainIdFactory::create(null));
        $this->assertInstanceOf(DomainId::class, DomainIdFactory::create(1));
        $this->assertInstanceOf(DomainId::class, DomainIdFactory::create($this->createMock(DomainIdInterface::class)));
        $this->assertInstanceOf(DomainId::class, DomainIdFactory::create('vgi00000000-0000-0000-0000-00000000000'));
        $this->assertInstanceOf(DomainUuid::class, DomainIdFactory::create($this->createMock(UuidInterface::class)));
        $this->assertInstanceOf(DomainUuid::class, DomainIdFactory::create('00000000-0000-0000-0000-000000000000'));
    }
}
