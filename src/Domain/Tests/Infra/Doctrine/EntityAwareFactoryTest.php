<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\ORM\Proxy\Proxy;
use MsgPhp\Domain\Factory\EntityAwareFactoryInterface;
use MsgPhp\Domain\Tests\Fixtures\Entities;
use MsgPhp\Domain\Infra\Doctrine\EntityAwareFactory;
use PHPUnit\Framework\TestCase;

final class EntityAwareFactoryTest extends TestCase
{
    use EntityManagerTrait;

    private $createSchema = false;

    public function testReference(): void
    {
        $factory = new EntityAwareFactory($this->createMock(EntityAwareFactoryInterface::class), self::$em);

        $this->assertInstanceOf(Proxy::class, $ref = $factory->reference(Entities\TestEntity::class, 1));
        $this->assertInstanceOf(Entities\TestEntity::class, $ref);
    }
}
