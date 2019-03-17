<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Model;

use MsgPhp\User\Entity\User;
use MsgPhp\User\Model\UserField;
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserFieldTest extends TestCase
{
    public function testField(): void
    {
        $value = $this->createMock(User::class);
        $value->expects(self::any())
            ->method('getId')
            ->willReturn($this->createMock(UserIdInterface::class))
        ;

        $object = $this->getObject($value);

        self::assertSame($value, $object->getUser());
        self::assertSame($value->getId(), $object->getUserId());
    }

    /**
     * @return object
     */
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
