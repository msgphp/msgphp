<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeUserCredentialCommand
{
    public $userId;
    public $context;

    public function __construct($userId, array $context)
    {
        $this->userId = $userId;
        $this->context = $context;
    }
}
