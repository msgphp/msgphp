<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity\Fields;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\Fields\AttributeValuesField;
use MsgPhp\User\Entity\UserAttributeValue;
use PHPUnit\Framework\TestCase;

final class AttributeValuesFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($attributeValues = [$this->createMock(UserAttributeValue::class)]);

        $this->assertSame($attributeValues, iterator_to_array($object->getAttributeValues()));

        $object = $this->getObject($attributeValues = $this->createMock(DomainCollectionInterface::class));

        $this->assertSame($attributeValues, $object->getAttributeValues());
    }

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
