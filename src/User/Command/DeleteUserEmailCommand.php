<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class DeleteUserEmailCommand
{
    public $userId;
    public $email;

    final public function __construct($userId, string $email)
    {
        $this->userId = $userId;
        $this->email = $email;
    }
}
