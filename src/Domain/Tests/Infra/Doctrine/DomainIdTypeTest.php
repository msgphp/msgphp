<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Infra\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MsgPhp\Domain\Tests\Fixtures\{TestDomainId, TestOtherDomainId};
use MsgPhp\Domain\Infra\Doctrine\DomainIdType;
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
        if (Type::hasType('domain_id')) {
            Type::overrideType('domain_id', DomainIdType::class);
        } else {
            Type::addType('domain_id', DomainIdType::class);
        }

        if (Type::hasType('other_domain_id')) {
            Type::overrideType('other_domain_id', TestOtherDomainIdType::class);
        } else {
            Type::addType('other_domain_id', TestOtherDomainIdType::class);
        }
    }

    protected function setUp(): void
    {
        DomainIdType::resetMapping();
        DomainIdType::setClass(TestDomainId::class);

        $this->type = Type::getType('domain_id');
        $this->otherType = Type::getType('other_domain_id');
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
        self::assertSame(DomainIdType::NAME, $this->type->getName());
        self::assertSame(TestOtherDomainIdType::NAME, $this->otherType->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        TestOtherDomainIdType::setDataType(Type::STRING);

        self::assertSame('native_integer_type', $this->type->getSQLDeclaration([], $this->platform));
        self::assertSame(Type::INTEGER, DomainIdType::getDataType());
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

class TestOtherDomainIdType extends DomainIdType
{
    public const NAME = 'other_domain_id';
}
