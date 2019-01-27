<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class RequestUserPasswordCommand
{
    public $userId;
    public $token;

    final public function __construct(UserIdInterface $userId, string $token = null)
    {
        $this->userId = $userId;
        $this->token = $token;
    }
}
