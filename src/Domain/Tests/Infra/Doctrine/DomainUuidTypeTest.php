<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use MsgPhp\Domain\Infra\Doctrine\DomainUuidType;
use MsgPhp\Domain\Infra\Uuid\DomainId;
use PHPUnit\Framework\TestCase;

final class DomainUuidTypeTest extends TestCase
{
    private const NIL_UUID = '00000000-0000-0000-0000-000000000000';

    /** @var AbstractPlatform */
    private $platform;

    public static function setUpBeforeClass(): void
    {
        DomainUuidType::addType('uuid', DomainUuidType::class);
    }

    protected function setUp(): void
    {
        $this->platform = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    public function testConvertToPHPValue(): void
    {
        /** @var DomainUuidType $type */
        $type = DomainUuidType::getType('uuid');

        $this->assertNull($type->convertToPHPValue(null, $this->platform));
        $this->assertInstanceOf(DomainId::class, $id = $type->convertToPHPValue(self::NIL_UUID, $this->platform));
        $this->assertSame(self::NIL_UUID, (string) $id);
    }

    public function testConvertToPHPValueWithInvalidType(): void
    {
        /** @var DomainUuidType $type */
        $type = DomainUuidType::getType('uuid');

        $this->expectException(ConversionException::class);

        $type->convertToPHPValue(123, $this->platform);
    }

    public function testConvertToPHPValueWithInvalidUuid(): void
    {
        /** @var DomainUuidType $type */
        $type = DomainUuidType::getType('uuid');

        $this->expectException(ConversionException::class);

        $type->convertToPHPValue('123', $this->platform);
    }

    public function testConvertToDatabaseValue(): void
    {
        /** @var DomainUuidType $type */
        $type = DomainUuidType::getType('uuid');

        $this->assertNull($type->convertToDatabaseValue(null, $this->platform));
        $this->assertSame(self::NIL_UUID, $type->convertToDatabaseValue(new DomainId(self::NIL_UUID), $this->platform));
    }

    public function testConvertToDatabaseValueWithInvalidType(): void
    {
        /** @var DomainUuidType $type */
        $type = DomainUuidType::getType('uuid');

        $this->expectException(ConversionException::class);

        $type->convertToDatabaseValue(self::NIL_UUID, $this->platform);
    }
}
