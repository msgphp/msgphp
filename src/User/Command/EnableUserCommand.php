<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class EnableUserCommand
{
    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }
}
