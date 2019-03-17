<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Model;

use MsgPhp\Domain\DomainCollectionInterface;
use MsgPhp\User\Entity\UserRole;
use MsgPhp\User\Model\RolesField;
use PHPUnit\Framework\TestCase;

final class RolesFieldTest extends TestCase
{
    public function testField(): void
    {
        $object = $this->getObject($roles = [$this->createMock(UserRole::class)]);

        self::assertInstanceOf(DomainCollectionInterface::class, $collection = $object->getRoles());
        self::assertSame($roles, iterator_to_array($collection));
        self::assertNotSame($collection = $this->createMock(DomainCollectionInterface::class), $this->getObject($collection)->getRoles());
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
