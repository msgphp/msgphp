<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class ChangeUserCredentialCommand
{
    public $userId;
    public $context;

    final public function __construct(UserIdInterface $userId, array $context)
    {
        $this->userId = $userId;
        $this->context = $context;
    }
}
