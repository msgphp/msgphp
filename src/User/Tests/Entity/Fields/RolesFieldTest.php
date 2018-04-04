<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity\Fields;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\Fields\RolesField;
use MsgPhp\User\Entity\UserRole;
use PHPUnit\Framework\TestCase;

final class RolesFieldTest extends TestCase
{
    public function testGetRoles(): void
    {
        $object = $this->getObject($roles = [$this->createMock(UserRole::class)]);

        $this->assertInstanceOf(DomainCollectionInterface::class, $collection = $object->getRoles());
        $this->assertSame($roles, iterator_to_array($collection));
        $this->assertSame($collection = $this->createMock(DomainCollectionInterface::class), $this->getObject($collection)->getRoles());
    }

    private function getObject($value)
    {
        return new class($value) {
            use RolesField;

            public function __construct($value)
            {
                $this->roles = $value;
            }
        };
    }
}
