<?php

declare(strict_types=1);

namespace MsgPhp\User\Tests;

use MsgPhp\User\Credential;
use MsgPhp\User\User;
use MsgPhp\User\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testGetCredential(): void
    {
        self::assertInstanceOf(Credential\Anonymous::class, $this->createEntity($this->createMock(UserId::class))->getCredential());
    }

    private function createEntity($id): User
    {
        return new class($id) extends User {
            private $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function getId(): UserId
            {
                return $this->id;
            }
        };
    }
}
