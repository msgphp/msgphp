<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Model;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\UserAttributeValue;
use MsgPhp\User\Model\AttributeValuesField;
use PHPUnit\Framework\TestCase;

final class AttributeValuesFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($attributeValues = [$this->createMock(UserAttributeValue::class)]);

        self::assertSame($attributeValues, iterator_to_array($object->getAttributeValues()));

        $object = $this->getObject($attributeValues = $this->createMock(DomainCollectionInterface::class));

        self::assertNotSame($attributeValues, $object->getAttributeValues());
    }

    /**
     * @return object
     */
    private function getObject($value)
    {
        return new class($value) {
            use AttributeValuesField;

            public function __construct($value)
            {
                $this->attributeValues = $value;
            }
        };
    }
}
