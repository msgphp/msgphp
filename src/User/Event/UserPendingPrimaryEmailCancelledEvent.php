<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\Entity\UserSecondaryEmail;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserPendingPrimaryEmailCancelledEvent
{
    public $userSecondaryEmail;

    public function __construct(UserSecondaryEmail $userSecondaryEmail)
    {
        $this->userSecondaryEmail = $userSecondaryEmail;
    }
}
