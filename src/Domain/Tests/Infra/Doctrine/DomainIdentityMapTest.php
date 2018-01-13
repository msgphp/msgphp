<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Infra\Doctrine\DomainIdentityMap;
use PHPUnit\Framework\TestCase;

final class DomainIdentityMapTest extends TestCase
{
    private $em;

    protected function setUp(): void
    {
        $factory = $this->createMock(ClassMetadataFactory::class);
        $factory->expects($this->any())
            ->method('hasMetadataFor')
            ->willReturnCallback(function ($class) {
                return \stdClass::class === $class;
            });
        $factory->expects($this->any())
            ->method('getMetadataFor')
            ->willReturnCallback(function ($class) {
                if (\stdClass::class !== $class) {
                    throw new \UnexpectedValueException();
                }

                $metadata = $this->createMock(ClassMetadata::class);
                $metadata->expects($this->any())
                    ->method('getIdentifierFieldNames')
                    ->willReturn(['id']);
                $metadata->expects($this->any())
                    ->method('getIdentifierValues')
                    ->willReturn(['id' => 1]);

                return $metadata;
            });

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->expects($this->any())
            ->method('getMetadataFactory')
            ->willReturn($factory);
    }

    public function testGetIdentifierFieldNames(): void
    {
        $this->assertSame(['id'], (new DomainIdentityMap($this->em))->getIdentifierFieldNames(\stdClass::class));
    }

    public function testGetIdentifierFieldNamesWithInvalidClass(): void
    {
        $map = new DomainIdentityMap($this->em);

        $this->expectException(InvalidClassException::class);

        $map->getIdentifierFieldNames('foo');
    }

    public function testGetIdentity(): void
    {
        $this->assertSame(['id' => 1], (new DomainIdentityMap($this->em))->getIdentity(new \stdClass()));
    }

    public function testGetIdentityWithInvalidClass(): void
    {
        $map = new DomainIdentityMap($this->em);

        $this->expectException(InvalidClassException::class);

        $map->getIdentity(new class() {
        });
    }
}
