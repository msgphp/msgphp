<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\Entity\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class UserAttributeValueChangedEvent
{
    public $userAttributeValue;
    public $oldValue;

    public function __construct(UserAttributeValue $userAttributeValue, $oldValue)
    {
        $this->userAttributeValue = $userAttributeValue;
        $this->oldValue = $oldValue;
    }
}
