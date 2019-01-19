<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\{DomainObjectFactoryInterface, EntityAwareFactory};
use PHPUnit\Framework\TestCase;

final class EntityAwareFactoryTest extends TestCase
{
    /** @var EntityAwareFactory */
    private $factory;

    protected function setUp(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::any())
            ->method('create')
            ->willReturnCallback(function ($class, array $context) {
                if ('id' === $class) {
                    if ([] === $context || [null] === $context) {
                        $id = $this->createMock(DomainIdInterface::class);
                        $id->expects(self::any())
                            ->method('toString')
                            ->willReturn('new')
                        ;

                        return $id;
                    }

                    if (1 !== \count($context)) {
                        self::fail();
                    }
                    $id = $this->createMock(DomainIdInterface::class);
                    $id->expects(self::any())
                        ->method('toString')
                        ->willReturn(reset($context))
                    ;

                    return $id;
                }

                $o = new \stdClass();
                $o->class = $class;
                $o->context = $context;

                return $o;
            })
        ;
        $innerFactory->expects(self::any())
            ->method('getClass')
            ->willReturnArgument(0)
        ;

        $identityMapping = $this->createMock(DomainIdentityMappingInterface::class);
        $identityMapping->expects(self::any())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id_field', 'id_field2'])
        ;

        $this->factory = new EntityAwareFactory($innerFactory, new DomainIdentityHelper($identityMapping));
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(\stdClass::class, $object = $this->factory->create('foo'));
        self::assertSame(['class' => 'foo', 'context' => []], (array) $object);
        self::assertInstanceOf(\stdClass::class, $object = $this->factory->create('bar', ['context']));
        self::assertSame(['class' => 'bar', 'context' => ['context']], (array) $object);
    }

    public function testGetClass(): void
    {
        self::assertSame('foo', $this->factory->getClass('foo'));
        self::assertSame('bar', $this->factory->getClass('bar', ['context']));
    }

    public function testReference(): void
    {
        /** @var TestReferencedEntity $reference */
        $reference = $this->factory->reference(TestReferencedEntity::class, ['id_field' => 1, 'id_field2' => 2]);

        self::assertInstanceOf(TestReferencedEntity::class, $reference);
        self::assertSame([1, 2], $reference->get());
    }

    public function testReferenceWithUnknownClass(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->factory->reference('foo', ['id_field' => 1, 'id_field2' => 2]);
    }

    public function testReferenceWithInvalidIdentity(): void
    {
        $this->expectException(\LogicException::class);

        $this->factory->reference(TestReferencedEntity::class, 1);
    }
}

class TestReferencedEntity
{
    private $idField;
    private $idField2 = 'foo';

    public function __construct()
    {
        throw new \BadMethodCallException();
    }

    public function get(): array
    {
        return [$this->idField, $this->idField2];
    }
}
