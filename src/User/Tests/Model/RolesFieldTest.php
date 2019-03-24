<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Model;

use MsgPhp\Domain\DomainCollection;
use MsgPhp\User\Model\RolesField;
use MsgPhp\User\UserRole;
use PHPUnit\Framework\TestCase;

final class RolesFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($roles = [$this->createMock(UserRole::class)]);

        self::assertInstanceOf(DomainCollection::class, $collection = $object->getRoles());
        self::assertSame($roles, iterator_to_array($collection));
        self::assertNotSame($collection = $this->createMock(DomainCollection::class), $this->getObject($collection)->getRoles());
    }

    /**
     * @return object
     */
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
