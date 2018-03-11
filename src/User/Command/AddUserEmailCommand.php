<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserEmailCommand
{
    public $userId;
    public $email;
    public $context;

    final public function __construct($userId, string $email, array $context = [])
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->context = $context;
    }
}
