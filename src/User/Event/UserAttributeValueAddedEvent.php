<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\Entity\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserAttributeValueAddedEvent
{
    public $userAttributeValue;

    public function __construct(UserAttributeValue $userAttributeValue)
    {
        $this->userAttributeValue = $userAttributeValue;
    }
}
