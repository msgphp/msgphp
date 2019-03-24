<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class EnableUser
{
    /**
     * @var UserId
     */
    public $userId;

    final public function __construct(UserId $userId)
    {
        $this->userId = $userId;
    }
}
