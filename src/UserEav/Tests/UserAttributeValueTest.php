<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests;

use MsgPhp\Eav\AttributeValue;
use MsgPhp\Eav\AttributeValueIdInterface;
use MsgPhp\User\User;
use MsgPhp\User\UserAttributeValue;
use PHPUnit\Framework\TestCase;

final class UserAttributeValueTest extends TestCase
{
    public function testCreate(): void
    {
        $attributeValue = $this->createMock(AttributeValue::class);
        $attributeValue->expects(self::once())
            ->method('getId')
            ->willReturn($id = $this->createMock(AttributeValueIdInterface::class))
        ;
        $userAttributeValue = $this->createEntity($user = $this->createMock(User::class), $attributeValue);

        self::assertSame($user, $userAttributeValue->getUser());
        self::assertSame($id, $userAttributeValue->getId());
    }

    private function createEntity($user, $attributeValue): UserAttributeValue
    {
        return new class($user, $attributeValue) extends UserAttributeValue {
        };
    }
}
