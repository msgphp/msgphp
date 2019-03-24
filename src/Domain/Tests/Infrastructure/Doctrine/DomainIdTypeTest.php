<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infrastructure\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\Infrastructure\Doctrine\DomainIdType;
use MsgPhp\Domain\Tests\Fixtures\TestDomainId;
use MsgPhp\Domain\Tests\Fixtures\TestDomainIdType;
use MsgPhp\Domain\Tests\Fixtures\TestOtherDomainId;
use MsgPhp\Domain\Tests\Fixtures\TestOtherDomainIdType;
use PHPUnit\Framework\TestCase;

final class DomainIdTypeTest extends TestCase
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var Type
     */
    private $otherType;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    public static function setUpBeforeClass(): void
    {
        if (Type::hasType(TestDomainIdType::NAME)) {
            Type::overrideType(TestDomainIdType::NAME, TestDomainIdType::class);
        } else {
            Type::addType(TestDomainIdType::NAME, TestDomainIdType::class);
        }

        if (Type::hasType(TestOtherDomainIdType::NAME)) {
            Type::overrideType(TestOtherDomainIdType::NAME, TestOtherDomainIdType::class);
        } else {
            Type::addType(TestOtherDomainIdType::NAME, TestOtherDomainIdType::class);
        }
    }

    protected function setUp(): void
    {
        DomainIdType::resetMapping();
        TestDomainIdType::setClass(TestDomainId::class);

        $this->type = Type::getType(TestDomainIdType::NAME);
        $this->otherType = Type::getType(TestOtherDomainIdType::NAME);
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->platform->expects(self::any())
            ->method('getIntegerTypeDeclarationSQL')
            ->willReturn('native_integer_type')
        ;
        $this->platform->expects(self::any())
            ->method('getVarcharTypeDeclarationSQL')
            ->willReturn('native_string_type')
        ;
    }

    protected function tearDown(): void
    {
        DomainIdType::resetMapping();
    }

    public function testSetClass(): void
    {
        TestOtherDomainIdType::setClass(TestOtherDomainId::class);

        self::assertSame(TestOtherDomainId::class, TestOtherDomainIdType::getClass());
    }

    public function testSetClassWithInvalidClass(): void
    {
        $this->expectException(\LogicException::class);

        /** @psalm-suppress InvalidArgument */
        DomainIdType::setClass(\stdClass::class);
    }

    public function testGetClassWithoutClassSet(): void
    {
        $this->expectException(\LogicException::class);

        TestOtherDomainIdType::getClass();
    }

    public function testGetName(): void
    {
        self::assertSame(TestDomainIdType::NAME, $this->type->getName());
        self::assertSame(TestOtherDomainIdType::NAME, $this->otherType->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        TestOtherDomainIdType::setDataType(Type::STRING);

        self::assertSame('native_integer_type', $this->type->getSQLDeclaration([], $this->platform));
        self::assertSame(Type::INTEGER, TestDomainIdType::getDataType());
        self::assertSame('native_string_type', $this->otherType->getSQLDeclaration([], $this->platform));
        self::assertSame(Type::STRING, TestOtherDomainIdType::getDataType());
    }

    public function testConvertToDatabaseValue(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        self::assertSame('1', $this->type->convertToDatabaseValue(new TestDomainId('1'), $this->platform));
        self::assertSame('1', $this->type->convertToDatabaseValue('1', $this->platform));
    }

    public function testConvertToPHPValue(): void
    {
        TestOtherDomainIdType::setDataType('string');
        TestOtherDomainIdType::setClass(TestOtherDomainId::class);

        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
        self::assertEquals(new TestDomainId('0'), $this->type->convertToPHPValue('foo', $this->platform));
        self::assertEquals(new TestDomainId('1'), $this->type->convertToPHPValue('1', $this->platform));
        self::assertEquals(new TestOtherDomainId('foo'), $this->otherType->convertToPHPValue('foo', $this->platform));
        self::assertEquals(new TestOtherDomainId('1'), $this->otherType->convertToPHPValue('1', $this->platform));
    }
}
