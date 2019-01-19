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
    public $fields;

    final public function __construct(UserIdInterface $userId, array $fields)
    {
        $this->userId = $userId;
        $this->fields = $fields;
    }
}
