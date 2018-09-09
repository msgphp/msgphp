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
        self::assertInstanceOf(DomainId::class, DomainIdFactory::create(null));
        self::assertInstanceOf(DomainId::class, DomainIdFactory::create(1));
        self::assertInstanceOf(DomainId::class, DomainIdFactory::create('vgi00000000-0000-0000-0000-00000000000'));
        self::assertInstanceOf(DomainUuid::class, DomainIdFactory::create($this->createMock(UuidInterface::class)));
        self::assertInstanceOf(DomainUuid::class, DomainIdFactory::create('00000000-0000-0000-0000-000000000000'));
        self::assertSame($id = $this->createMock(DomainIdInterface::class), DomainIdFactory::create($id));
    }
}
