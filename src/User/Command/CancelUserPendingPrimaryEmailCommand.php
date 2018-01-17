<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class CancelUserPendingPrimaryEmailCommand
{
    public $userId;

    public function __construct(UserIdInterface $userId)
    {
        $this->userId = $userId;
    }
}
