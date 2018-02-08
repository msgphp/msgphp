<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class RequestUserPasswordCommand
{
    public $userId;
    public $token;

    public function __construct($userId, string $token = null)
    {
        $this->userId = $userId;
        $this->token = $token;
    }
}
