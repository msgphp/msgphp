<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\Entity\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserAttributeValueChangedEvent
{
    public $userAttributeValue;
    public $oldValue;
    public $newValue;

    final public function __construct(UserAttributeValue $userAttributeValue, $oldValue, $newValue)
    {
        $this->userAttributeValue = $userAttributeValue;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
}
