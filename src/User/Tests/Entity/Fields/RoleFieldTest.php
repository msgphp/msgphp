<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity\Fields;

use MsgPhp\User\Entity\Fields\RoleField;
use MsgPhp\User\Entity\Role;
use PHPUnit\Framework\TestCase;

final class RoleFieldTest extends TestCase
{
    public function testField(): void
    {
        $value = $this->createMock(Role::class);
        $value->expects($this->any())
            ->method('getName')
            ->willReturn('ROLE_FOO');

        $object = $this->getObject($value);

        $this->assertSame($value, $object->getRole());
        $this->assertSame('ROLE_FOO', $object->getRoleName());
    }

    private function getObject($value)
    {
        return new class($value) {
            use RoleField;

            public function __construct($value)
            {
                $this->role = $value;
            }
        };
    }
}
