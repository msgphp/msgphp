<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\Eav\Entity\AttributeValue;
use MsgPhp\User\Entity\{User, UserAttributeValue};
use PHPUnit\Framework\TestCase;

final class UserAttributeValueTest extends TestCase
{
    public function testCreate(): void
    {
        $userAttributeValue = $this->createEntity($user = $this->createMock(User::class), $attributeValue = $this->createMock(AttributeValue::class));

        $this->assertSame($user, $userAttributeValue->getUser());
        $this->assertSame($attributeValue, $userAttributeValue->getAttributeValue());
    }

    private function createEntity($user, $attributeValue): UserAttributeValue
    {
        return new class($user, $attributeValue) extends UserAttributeValue {
        };
    }
}
