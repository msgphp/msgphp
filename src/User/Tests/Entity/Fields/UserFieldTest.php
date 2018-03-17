<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity\Fields;

use MsgPhp\User\Entity\Fields\UserField;
use MsgPhp\User\Entity\User;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserFieldTest extends TestCase
{
    public function testGetUser(): void
    {
        $value = $this->createMock(User::class);
        $value->expects($this->any())
            ->method('getId')
            ->willReturn($this->createMock(UserIdInterface::class));

        $object = $this->getObject($value);

        $this->assertSame($value, $object->getUser());
        $this->assertSame($value->getId(), $object->getUserId());
    }

    private function getObject($value)
    {
        return new class($value) {
            use UserField;

            public function __construct($value)
            {
                $this->user = $value;
            }
        };
    }
}
