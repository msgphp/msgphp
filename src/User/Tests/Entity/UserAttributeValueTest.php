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
        $now = new \DateTime();
        $userAttributeValue = new UserAttributeValue($user = $this->createMock(User::class), $attributeValue = $this->createMock(AttributeValue::class));

        $this->assertSame($user, $userAttributeValue->getUser());
        $this->assertSame($attributeValue, $userAttributeValue->getAttributeValue());
        $this->assertGreaterThanOrEqual($now, $userAttributeValue->getCreatedAt());
    }
}
