<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserAttributeValueDeletedEvent
{
    /**
     * @var UserAttributeValue
     */
    public $userAttributeValue;

    final public function __construct(UserAttributeValue $userAttributeValue)
    {
        $this->userAttributeValue = $userAttributeValue;
    }
}
