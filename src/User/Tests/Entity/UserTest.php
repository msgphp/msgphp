<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests\Entity;

use MsgPhp\User\Entity\{Credential, User};
use MsgPhp\User\UserIdInterface;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testGetCredential(): void
    {
        $this->assertInstanceOf(Credential\Anonymous::class, $this->createEntity($this->createMock(UserIdInterface::class))->getCredential());
    }

    private function createEntity($id): User
    {
        return new class($id) extends User {
            private $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function getId(): UserIdInterface
            {
                return $this->id;
            }
        };
    }
}
