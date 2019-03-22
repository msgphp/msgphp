<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\UserAttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserAttributeValueChangedEvent
{
    /**
     * @var UserAttributeValue
     */
    public $userAttributeValue;

    /**
     * @var mixed
     */
    public $oldValue;

    /**
     * @var mixed
     */
    public $newValue;

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    final public function __construct(UserAttributeValue $userAttributeValue, $oldValue, $newValue)
    {
        $this->userAttributeValue = $userAttributeValue;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
}
